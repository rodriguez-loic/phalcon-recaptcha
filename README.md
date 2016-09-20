# phalcon-recaptcha

Simple library for using ReCAPTCHA v2 with Phalcon.

## Preparation

Add Recaptcha.php to your library folder.
Do not forget to load it in your DI:

If you have a Services.php in app:
```php
protected function initRecaptcha()
{
    return new Recaptcha();
}
```

Or directly with:
```php
$di->set('recaptcha', function(){
  return new Recaptcha();
});
```

Please, update Recaptcha library to use your own public and private keys. You can easily create them from:
https://www.google.com/recaptcha/admin/create

## How to use it?

First add at the bottom of your document's head: 
```html
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
```

Then, call in your form:
```php
{{ recaptcha.get() }}
```
You can also use array to specify: themes, type, size, tabindex, callback and expired-callback tags (more information: https://developers.google.com/recaptcha/docs/display section "Configuration").
```php
{{ recaptcha.get(['theme': 'dark']) }}
```

To check posted data, you just have to use in your controller:
```php
// Get response from API
$response = Recaptcha::check($this->request->getPost('g-recaptcha-response'));

if ($response) {
  // Captcha is valid - continue
} else {
  // Captcha is not valid
  $this->flash->error('Sorry, the captcha seems to be incorrect, please try again.');
  return $this->view->form = $form;
}
```

# For more information

You can check google documentation here: https://developers.google.com/recaptcha/docs/intro
