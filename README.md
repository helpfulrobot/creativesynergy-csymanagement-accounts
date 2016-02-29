# csymanagement-accounts

```composer require creativesynergy/csymanagement-accounts```

### installation

add to your mysite/_config/config.yml and change the secret_key to an long and random generated string


```
Account:
  secret_key: 'SOOOSECRET' # if changed, stored accounts will be lost
```

add ```@import '../../csymanagement-accounts/scss/pdf';``` to the end of csymanagemnt-client/scss/pdf.scss