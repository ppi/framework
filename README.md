# PPI Framework

[@website]:       http://www.ppi.io/                              "PPI Framework"
[@documentation]: http://www.ppi.io/docs/2.0/getting_started.html "PPI Framework Documentation"
[@download]:      http://www.ppi.io/files/ppi-skeletonapp-without-vendors.tar.gz
[@gitweb]:        https://github.com/ppi/framework                "ppi/framework"
[@h5bp]:          http://html5boilerplate.com/                    "HTML5 Boilerplate"
[@twbootstrap]:   http://twitter.github.com/bootstrap/            "Twitter Bootstrap"
[@php]:           http://php.net/                                 "PHP: Hypertext Preprocessor"
[@twitter]:       https://twitter.com/ppi_framework               "PPI Framework at Twitter"

[![Join the chat at https://gitter.im/ppi/framework](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/ppi/framework?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

* [Official website][@website]
* [Download][@download]
* [Documentation][@documentation]

## What is PPI?

[PPI][@website] is the the PHP Interoperability Framework. It provides an equal and open platform to empower PHP developers to pick the best tools from the best PHP frameworks

PPI bootstraps framework components for you from the top frameworks such as ZendFrameworks2, Symfony2, Laravel4, FuelPHP, Doctrine2

## Requirements

* [PHP][@php] 5.3.3 or above

## Installation

There is no actual "installation" of PPI, you can download it from
http://www.ppi.io/downloads and drop it in your document root.

## Contributing

PPI is an open source, community-driven project. If you'd like to contribute, check out our issues list. You can find us
on IRC, Google Plus or Twitter ([@ppi_framework][@twitter]).

If you're submitting a pull request, please do so on your own branch on [GitHub][@gitweb].
 
Start by forking the PPI Framework repository and cloning your fork locally:

    $ git clone git@github.com:YOUR_USERNAME/framework.git
    $ git remote add upstream git://github.com/ppi/framework.git
    $ git checkout -b feature/BRANCH_NAME master

After your work is finished rebase the feature branch and push it:

    $ git checkout master
    $ git fetch upstream
    $ git merge upstream/master
    $ git checkout feature/BRANCH_NAME
    $ git rebase master
    $ git push --force origin feature/BRANCH_NAME

Go to GitHub again and make a pull request on the ppi/framework repository. Thank you for making PPI better!

