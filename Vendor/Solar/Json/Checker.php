<?php
/**
 *
 * JSON validation class to determine syntactic correctness prior to decoding.
 *  
 * A port of JSON_checker.c designed to quickly scan a block of JSON text
 * and determine if it is syntactically correct.
 * 
 * Learn more about Pushdown automatons at
 * <http://en.wikipedia.org/wiki/Pushdown_automaton>
 * 
 * @category Solar
 * 
 * @package Solar_Json
 * 
 * @author Douglas Crockford <douglas@crockford.com>
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @copyright Copyright (c) 2005 JSON.org
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * The Software shall be used for Good, not Evil.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 * @version $Id: Checker.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
class Solar_Json_Checker extends Solar_Base
{
    /**
     * Error
     * @constant
     */
    const S_ERR = -1;
    
    /**
     * Space
     * @constant
     */
    const S_SPA = 0;
    
    /**
     * Other whitespace
     * @constant
     */
    const S_WSP = 1;
    
    /**
     * {
     * @constant
     */
    const S_LBE = 2;
    
    /**
     * }
     * @constant
     */
    const S_RBE = 3;
    
    /**
     * [
     * @constant
     */
    const S_LBT = 4;
    
    /**
     * ]
     * @constant
     */
    const S_RBT = 5;
    
    /**
     * :
     * @constant
     */
    const S_COL = 6;
    
    /**
     * ,
     * @constant
     */
    const S_COM = 7;
    
    /**
     * "
     * @constant
     */
    const S_QUO = 8;
    
    /**
     * \
     * @constant
     */
    const S_BAC = 9;
    
    /**
     * /
     * @constant
     */
    const S_SLA = 10;
    
    /**
     * +
     * @constant
     */
    const S_PLU = 11;
    
    /**
     * -
     * @constant
     */
    const S_MIN = 12;
    
    /**
     * .
     * @constant
     */
    const S_DOT = 13;
    
    /**
     * 0
     * @constant
     */
    const S_ZER = 14;
    
    /**
     * 123456789
     * @constant
     */
    const S_DIG = 15;
    
    /**
     * a
     * @constant
     */
    const S__A_ = 16;
    
    /**
     * b
     * @constant
     */
    const S__B_ = 17;
    
    /**
     * c
     * @constant
     */
    const S__C_ = 18;
    
    /**
     * d
     * @constant
     */
    const S__D_ = 19;
    
    /**
     * e
     * @constant
     */
    const S__E_ = 20;
    
    /**
     * f
     * @constant
     */
    const S__F_ = 21;
    
    /**
     * l
     * @constant
     */
    const S__L_ = 22;
    
    /**
     * n
     * @constant
     */
    const S__N_ = 23;
    
    /**
     * r
     * @constant
     */
    const S__R_ = 24;
    
    /**
     * s
     * @constant
     */
    const S__S_ = 25;
    
    /**
     * t
     * @constant
     */
    const S__T_ = 26;
    
    /**
     * u
     * @constant
     */
    const S__U_ = 27;
    
    /**
     * ABCDF
     * @constant
     */
    const S_A_F = 28;
    
    /**
     * E
     * @constant
     */
    const S_E = 29;
    
    /**
     * Everything else
     * @constant
     */
    const S_ETC = 30;
    
    /**
     * 
     * Map of 128 ASCII characters into the 32 character classes.
     * 
     * The remaining Unicode characters should be mapped to S_ETC.
     * 
     * @var array
     * 
     */
    protected $_ascii_class = array();
    
    /**
     * 
     * State transition table.
     * 
     * @var array
     * 
     */
    protected $_state_transition_table = array();
    
    /**
     * 
     * These modes can be pushed on the "pushdown automata" (PDA) stack.
     * 
     * @constant
     * 
     */
    const MODE_DONE     = 1;
    const MODE_KEY      = 2;
    const MODE_OBJECT   = 3;
    const MODE_ARRAY    = 4;
    
    /**
     * 
     * Max depth allowed for nested structures.
     * 
     * @constant
     * 
     */
    const MAX_DEPTH = 20;
    
    /**
     * 
     * The stack to maintain the state of nested structures.
     * 
     * @var array
     * 
     */
    protected $_the_stack = array();
    
    /**
     * 
     * Pointer for the top of the stack.
     * 
     * @var int
     * 
     */
    protected $_the_top;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        $this->_mapAscii();
        $this->_setStateTransitionTable();
    }
    
    /**
     * 
     * The isValid method takes a UTF-16 encoded string and determines if it is
     * a syntactically correct JSON text.
     * 
     * It is implemented as a Pushdown Automaton; that means it is a finite
     * state machine with a stack.
     * 
     * @param string $str The JSON text to validate
     * 
     * @return bool
     * 
     */
    public function isValid($str)
    {
        // string length
        $len = strlen($str);
        // the next character
        //$b = 0;
        // the next character class
        //$c = 0;
        // the next state
        //$s = 0;
        
        $_the_state = 0;
        $this->_the_top = -1;
        $this->_push(self::MODE_DONE);
        
        for ($_the_index = 0; $_the_index < $len; $_the_index++) {
            $b = $str{$_the_index};
            if (chr(ord($b) & 127) == $b) {
                $c = $this->_ascii_class[ord($b)];
                if ($c <= self::S_ERR) {
                    return false;
                }
            } else {
                $c = self::S_ETC;
            }
            
            // Get the next state from the transition table
            $s = $this->_state_transition_table[$_the_state][$c];
            
            if ($s < 0) {
                // Perform one of the predefined actions
                
                switch($s) {
                    
                    // empty }
                    case -9:
                        if (!$this->_pop(self::MODE_KEY)) {
                            return false;
                        }
                        $_the_state = 9;
                        break;
                    
                    // {
                    case -8:
                        if (!$this->_push(self::MODE_KEY)) {
                            return false;
                        }
                        $_the_state = 1;
                        break;
                    
                    // }
                    case -7:
                        if (!$this->_pop(self::MODE_OBJECT)) {
                            return false;
                        }
                        $_the_state = 9;
                        break;
                    
                    // [
                    case -6:
                        if (!$this->_push(self::MODE_ARRAY)) {
                            return false;
                        }
                        $_the_state = 2;
                        break;
                    
                    // ]
                    case -5:
                        if (!$this->_pop(self::MODE_ARRAY)) {
                            return false;
                        }
                        $_the_state = 9;
                        break;
                    
                    // "
                    case -4:
                        switch($this->_the_stack[$this->_the_top]) {
                            case self::MODE_KEY:
                                $_the_state = 27;
                                break;
                            case self::MODE_ARRAY:
                            case self::MODE_OBJECT:
                                $_the_state = 9;
                                break;
                            default:
                                return false;
                        }
                        break;
                    
                    // '
                    case -3:
                        switch($this->_the_stack[$this->_the_top]) {
                            case self::MODE_OBJECT:
                                if ($this->_pop(self::MODE_OBJECT) && $this->_push(self::MODE_KEY)) {
                                    $_the_state = 29;
                                }
                                break;
                            case self::MODE_ARRAY:
                                $_the_state = 28;
                                break;
                            default:
                                return false;
                        }
                        break;
                    
                    // :
                    case -2:
                        if ($this->_pop(self::MODE_KEY) && $this->_push(self::MODE_OBJECT)) {
                            $_the_state = 28;
                            break;
                        }
                    
                    // syntax error
                    case -1:
                        return false;
                }
            } else {
                
                // change the state and iterate
                $_the_state = $s;
            
            }
        
        }
        
        if ($_the_state == 9 && $this->_pop(self::MODE_DONE)) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * Map the 128 ASCII characters into the 32 character classes.
     * The remaining Unicode characters should be mapped to S_ETC.
     * 
     * @return void
     * 
     */
    protected function _mapAscii()
    {
        $this->_ascii_class = array(
            self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR,
            self::S_ERR, self::S_WSP, self::S_WSP, self::S_ERR, self::S_ERR, self::S_WSP, self::S_ERR, self::S_ERR,
            self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR,
            self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR, self::S_ERR,
            
            self::S_SPA, self::S_ETC, self::S_QUO, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC,
            self::S_ETC, self::S_ETC, self::S_ETC, self::S_PLU, self::S_COM, self::S_MIN, self::S_DOT, self::S_SLA,
            self::S_ZER, self::S_DIG, self::S_DIG, self::S_DIG, self::S_DIG, self::S_DIG, self::S_DIG, self::S_DIG,
            self::S_DIG, self::S_DIG, self::S_COL, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC,
            
            self::S_ETC, self::S_A_F, self::S_A_F, self::S_A_F, self::S_A_F, self::S_E  , self::S_A_F, self::S_ETC,
            self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC,
            self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC,
            self::S_ETC, self::S_ETC, self::S_ETC, self::S_LBT, self::S_BAC, self::S_RBT, self::S_ETC, self::S_ETC,
            
            self::S_ETC, self::S__A_, self::S__B_, self::S__C_, self::S__D_, self::S__E_, self::S__F_, self::S_ETC,
            self::S_ETC, self::S_ETC, self::S_ETC, self::S_ETC, self::S__L_, self::S_ETC, self::S__N_, self::S_ETC,
            self::S_ETC, self::S_ETC, self::S__R_, self::S__S_, self::S__T_, self::S__U_, self::S_ETC, self::S_ETC,
            self::S_ETC, self::S_ETC, self::S_ETC, self::S_LBE, self::S_ETC, self::S_RBE, self::S_ETC, self::S_ETC
        );
    }
    
    /**
     * 
     * The state transition table takes the current state and the current symbol,
     * and returns either a new state or an action. A new state is a number between
     * 0 and 29. An action is a negative number between -1 and -9. A JSON text is
     * accepted if the end of the text is in state 9 and mode is MODE_DONE.
     * 
     * @return void;
     * 
     */
    protected function _setStateTransitionTable()
    {
        $this->_state_transition_table = array(
            array( 0, 0,-8,-1,-6,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array( 1, 1,-1,-9,-1,-1,-1,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array( 2, 2,-8,-1,-6,-5,-1,-1, 3,-1,-1,-1,20,-1,21,22,-1,-1,-1,-1,-1,13,-1,17,-1,-1,10,-1,-1,-1,-1),
            array( 3,-1, 3, 3, 3, 3, 3, 3,-4, 4, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3),
            array(-1,-1,-1,-1,-1,-1,-1,-1, 3, 3, 3,-1,-1,-1,-1,-1,-1, 3,-1,-1,-1, 3,-1, 3, 3,-1, 3, 5,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 6, 6, 6, 6, 6, 6, 6, 6,-1,-1,-1,-1,-1,-1, 6, 6,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 7, 7, 7, 7, 7, 7, 7, 7,-1,-1,-1,-1,-1,-1, 7, 7,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 8, 8, 8, 8, 8, 8, 8, 8,-1,-1,-1,-1,-1,-1, 8, 8,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 3, 3, 3, 3, 3, 3, 3, 3,-1,-1,-1,-1,-1,-1, 3, 3,-1),
            array( 9, 9,-1,-7,-1,-5,-1,-3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,11,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,12,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 9,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,14,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,15,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,16,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 9,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,18,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,19,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, 9,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,21,22,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array( 9, 9,-1,-7,-1,-5,-1,-3,-1,-1,-1,-1,-1,23,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array( 9, 9,-1,-7,-1,-5,-1,-3,-1,-1,-1,-1,-1,23,22,22,-1,-1,-1,-1,24,-1,-1,-1,-1,-1,-1,-1,-1,24,-1),
            array( 9, 9,-1,-7,-1,-5,-1,-3,-1,-1,-1,-1,-1,-1,23,23,-1,-1,-1,-1,24,-1,-1,-1,-1,-1,-1,-1,-1,24,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,25,25,-1,26,26,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array(-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,26,26,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array( 9, 9,-1,-7,-1,-5,-1,-3,-1,-1,-1,-1,-1,-1,26,26,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array(27,27,-1,-1,-1,-1,-2,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1),
            array(28,28,-8,-1,-6,-1,-1,-1, 3,-1,-1,-1,20,-1,21,22,-1,-1,-1,-1,-1,13,-1,17,-1,-1,10,-1,-1,-1,-1),
            array(29,29,-1,-1,-1,-1,-1,-1, 3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1)
        );
    }
    
    /**
     * 
     * Push a mode onto the stack. Return false if there is overflow.
     * 
     * @param int $mode Mode to push onto the stack
     * 
     * @return bool Success/failure of stack push
     * 
     */
    protected function _push($mode)
    {
        ++$this->_the_top;
        if ($this->_the_top >= self::MAX_DEPTH) {
            return false;
        }
        $this->_the_stack[$this->_the_top] = $mode;
        return true;
    }
    
    /**
     * 
     * Pop the stack, assuring that the current mode matches the expectation.
     * Return false if there is underflow or if the modes mismatch.
     * 
     * @param int $mode Mode to pop from the stack
     * 
     * @return bool Success/failure of stack pop
     * 
     */
    protected function _pop($mode)
    {
        if ($this->_the_top < 0 || $this->_the_stack[$this->_the_top] != $mode) {
            return false;
        }
        $this->_the_stack[$this->_the_top] = 0;
        --$this->_the_top;
        return true;
    }
}
