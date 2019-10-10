Contributing
============

Thank you for contributing to this project!

Bug reports
-----------

If you find a bug, please submit an issue. Try to be as detailed as possible
in your problem description to help us fix the bug.

Feature requests
----------------

If you wish to propose a feature, please submit an issue. Try to explain your
use case as fully as possible to help us understand why you think the feature
should be added.

Creating a pull request (PR)
----------------------------

First [fork the repository](https://help.github.com/articles/fork-a-repo/) on
GitHub.

Then clone your fork:

```bash
$ git clone https://github.com/code-rhapsodie/dataflow-bundle.git
$ git checkout -b bug-or-feature-description
```

And install the dependencies:

```bash
$ composer install
```

Write your code and add tests. Then run the tests:

```bash
$ vendor/bin/phpunit
```

Commit your changes and push them to GitHub:

```bash
$ git commit -m "Fix nasty bug"
$ git push -u origin bug-or-feature-description
```

Then [create a pull request](https://help.github.com/articles/creating-a-pull-request/)
on GitHub.

If you need to make some changes, commit and push them as you like. When asked
to squash your commits, do so as follows:

```bash
git rebase -i
git push origin bug-or-feature-description -f
```

Coding standard
---------------

This project follows the [Symfony](https://symfony.com/doc/current/contributing/code/standards.html) coding style.
Please make sure your pull requests adhere to this standard.

To fix, execute this command after [download PHP CS Fixer](https://cs.symfony.com/):

```shell script
$ php php-cs-fixer.phar fix
```
