# phalcon-recaptcha

Simple library rewritted (from: https://github.com/pavlosadovyi/phalcon-recaptcha) for using ReCAPTCHA v2 with Phalcon.

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

## How to use it?

First add at the bottom of your document's head: 

```html
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
```

Then, call in your form:

```php
{{ recaptcha.get() }}
```

To check posted data, you just have to use in your controller:

```php
// Get response from API
$response = Recaptcha::check(
  $this->request->getPost('recaptcha_challenge_field'),
  $this->request->getPost('recaptcha_response_field')
);

if ($response) {
  // Captcha is valid - continue
} else {
  // Captcha is not valid
  $this->flash->error('Sorry, the captcha seems to be incorrect, please try again.');
  return $this->view->form = $form;
}
```
