# CONTRIBUTING

[@gitter]:        https://gitter.im/ppi/framework                           "Gitter"
[@gitweb]:        https://github.com/ppi/framework                          "ppi/framework"
[@gplus]:         https://plus.google.com/communities/100606932222119087997 "PPI on Google+"
[@twitter]:       https://twitter.com/ppi_framework                         "PPI Framework at Twitter"

PPI is an open source, community-driven project. If you'd like to contribute, check out our issues list. You can find us
on [Gitter][@gitter], IRC, [Google Plus][@gplus] or Twitter ([@ppi_framework][@twitter]).

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

Go to GitHub again and make a pull request on the `ppi/framework` repository. Thank you for making PPI better!
