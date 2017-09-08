Hmm, how do I put this?
1. Work really hard to get the HTML Email CSS right
2. In config.yml, define where your CSS is
```
Email:
  CSSPath:
    '/themes/default/css/email.css'
```
3. Create email objects extending SaltedEmail
4. ->send()
