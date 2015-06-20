```php
INSTALL_DIR="/usr/local/phpgit"
git clone https://this-repo "$INSTALL_DIR"
ln -s "$INSTALL_DIR/bin/diff" /usr/local/bin/git-phpdiff

git phpdiff HEAD~30 HEAD
```
