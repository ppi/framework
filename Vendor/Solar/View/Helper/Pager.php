<?php
/**
 * 
 * Helper to build a list of pager links.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Pager.php 4575 2010-05-15 21:46:16Z pmjones $
 * 
 */
class Solar_View_Helper_Pager extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string list_type The type of list to use; default is 'ul'. Only 'ul' and 'ol'
     *   are honored.
     * 
     * @config string div_id The CSS ID for the <div> wrapping the list. Default empty.
     * 
     * @config string div_class The CSS class for the <div> wrapping the list. Default is
     *   'pager'.
     * 
     * @config string prev The locale key for the "previous" link text.  Default is
     *   'PAGER_PREV'.
     * 
     * @config string next The locale key for the "next" link text.  Default is
     *   'PAGER_NEXT'.
     * 
     * @config string prev_class The CSS class for the previous-page <a> tag. Default is
     *   'prev'.
     * 
     * @config string curr_class The CSS class for the current-page <a> tag. Default is 
     *   'curr'.
     * 
     * @config string next_class The CSS class for the next-page <a> tag. Default is 'next'.
     * 
     * @config string style_href An HREF to the pager stylesheet to load. Default is
     *   'Solar/View/Helper/Pager/style.css'.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_Pager = array(
        'list_type'  => 'ul',
        'div_id'     => '',
        'div_class'  => 'pager',
        'prev'       => 'PAGER_PREV',
        'next'       => 'PAGER_NEXT',
        'prev_class' => 'prev',
        'curr_class' => 'curr',
        'next_class' => 'next',
        'style_href' => 'Solar/View/Helper/Pager/style.css',
    );
    
    /**
     * 
     * Returns a list of pager links.
     * 
     * @param array $data An associative array of data for the pager with keys
     * for 'count' (total number of items), 'pages' (number of pages), 'paging'
     * (the number of items per page), and 'page' (the current page number).
     * 
     * @param array $config Configuration value overrides, if any.
     * config.
     * 
     * @return string
     * 
     */
    public function pager($data, $config = null)
    {
        // info for the pager
        $count  = $data['count'];
        $pages  = $data['pages'];
        $paging = $data['paging'];
        $page   = $data['page'];
        
        // output config
        $config = array_merge($this->_config, (array) $config);
        
        // do we really need paging?
        if ($pages <= 1) {
            // zero or one pages, nothing to do here
            return;
        }
        
        // add the pager stylesheet
        $this->_view->head()->addStyle($config['style_href']);
        
        // make sure we have ol or ul
        $list_type = strtolower($config['list_type']);
        if ($list_type != 'ol') {
            $list_type = 'ul';
        }
        
        // get the base href to work with, and use str_replace on it later.
        // this will be faster than calling $uri->get() multiple times.
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->query['page'] = '__PAGE__';
        $base = $uri->get();
        
        // html we're building
        $html = array();
        
        // start the div
        $attribs = $this->_view->attribs(array(
            'id'    => $config['div_id'],
            'class' => $config['div_class'],
        ));
        
        $html[] = "<div$attribs>";
        
        // start the list
        $html[] = "<$list_type>";
        
        // show the "prev" link?
        $html[] = "    <li>";
        if ($page > 1) {
            $href = str_replace('__PAGE__', $page - 1, $base);
            $html[] = $this->_view->action($href, $config['prev'],
                array('class' => $config['prev_class']));
        } else {
            $html[] = $this->_view->getText($config['prev']);
        }
        $html[] = "</li>";
        
        // build the list of page links
        $list = $this->_getPageList($page, $pages);
        foreach ($list as $item) {
    
            if ($item == '...') {
                $html[] = "    <li>...</li>";
                continue;
            }
    
            if ($item == $page) {
                $attribs = array('class' => $config['curr_class']);
            } else {
                $attribs = array();
            }
    
            $href = str_replace('__PAGE__', $item, $base);
            $html[] = "    <li>"
               . $this->_view->action($href, (string) $item, $attribs)
               . "</li>";
        }
        
        // show the "next" link?
        $html[] = "    <li>";
        if ($page < $pages) {
            $href = str_replace('__PAGE__', $page + 1, $base);
            $html[] = $this->_view->action($href, $config['next'],
                array('class' => $config['next_class']));
        } else {
            $html[] = $this->_view->getText($config['next']);
        }
        $html[] = "</li>";
        
        // close the list and div, and done
        $html[] = "</$list_type>";
        $html[] = "</div>";
        return implode("\n", $html);
    }
    
    /**
     * 
     * Returns a list of page-number links to use, with ellipsis as needed.
     * 
     * @param int $page The current page number.
     * 
     * @param int $pages The total number of pages.
     * 
     * @return array
     * 
     */
    protected function _getPageList($page, $pages)
    {
        // keep a list of 11 items
        $list = array();
        
        // how to show them?
        if ($pages <= 11) {
            // 11 or fewer items
            $list = range(1, $pages);
        } elseif ($page < 8) {
            // early in the list
            $list = array(
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                '...',
                $pages - 1,
                $pages,
            );
        } elseif ($page > $pages - 8) {
            // late in the list
            $list = array(
                1,
                2,
                '...',
                $pages - 7,
                $pages - 6,
                $pages - 5,
                $pages - 4,
                $pages - 3,
                $pages - 2,
                $pages - 1,
                $pages,
            );
        } else {
            // mid-list
            $list = array(
                1,
                2,
                '...',
                $page - 2,
                $page - 1,
                $page,
                $page + 1,
                $page + 2,
                '...',
                $pages - 1,
                $pages,
            );
        }
        
        // done!
        return $list;
    }
}