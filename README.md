# csymanagement-accounts

```composer require creativesynergy/csymanagement-accounts```

### installation

add to your mysite/_config/config.yml and change the secret_key to an long and random generated string

```
Account:
  secret_key: 'SECRET256BitKey' # if changed, stored accounts will be lost
```

compile scss under ```csymanagement-client```