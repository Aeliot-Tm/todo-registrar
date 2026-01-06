### Downloading of PHAR directly

Download PHAR directly to root directory of the project or in another place as you wish.
```shell
# Do adjust the URL if you need a release other than the latest
wget -O todo-registrar.phar "https://github.com/Aeliot-Tm/todo-registrar/releases/latest/download/todo-registrar.phar"

# make executable
chmod +x todo-registrar.phar
```

Additionally, you may validate downloaded PHAR file with GPG signature:
```shell
# Do adjust the URL if you need a release other than the latest
wget -O todo-registrar.phar.asc "https://github.com/Aeliot-Tm/todo-registrar/releases/latest/download/todo-registrar.phar.asc"

# Check that the signature matches
gpg --verify todo-registrar.phar.asc todo-registrar.phar

# Check the issuer (the ID can also be found from the previous command)
gpg --keyserver hkps://keys.openpgp.org --recv-keys 9D0DD6FCB92C84688B777DF59204DEE8CAE9C22C

rm todo-registrar.phar.asc
```
