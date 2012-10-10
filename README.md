Mobitex SMS Api
===============

About
-----

See:
  * http://smscenter.pl
  * http://smscenter.pl/specyfikacja_mt.pdf



Usage
-----

```php
<?php

$mobitex = \Mobitex\Sender::create($username, md5($password), $fromName);
try {
    $mobitex->sendMessage("+48 501 100 100", "Hello World!")
} catch (\Mobitex\Exception\PaymentRequired $e) {
    echo "Out of money";
} catch (\Mobitex\Exception\Forbidden $e) {
    echo "Invalid $fromName";
} catch (\Mobitex\Exception\RequestEntityTooLarge $e) {
    echo "Text message is too long!";
} catch (\Mobitex\Exception\Unauthorized $e) {
    echo "Invalid username or password!";
} catch (\Mobitex\Exception $e)
    echo 'Error: ' . $e->getMessage();
}
```

Custom Message Types
--------------------

  * \Mobitex\Sender::TYPE_SMS — simple text message
  * \Mobitex\Sender::TYPE_CONCAT — long text message (up to three packets)
  * \Mobitex\Sender::TYPE_UNICODE — with unicode support (two bytes per char)
  * \Mobitex\Sender::TYPE_UNICODE — long message with unicode support
  * \Mobitex\Sender::TYPE_WAP_PUSH — wap push (no special support yet!)
  * \Mobitex\Sender::TYPE_FLASH — not a text message, just a flash (won’t save to phone memory, etc)
  * \Mobitex\Sender::TYPE_BINARY 

Usage:
```php
<?php

$sender = \Mobitex\Sender::create($username, md5($password), $fromName);
$sender->sendMessage("+48 501 100 100", "Hello world!", \Mobitex\Sender::TYPE_FLASH);
```

Checking Account Balance
------------------------

```php
<?php

$sender = \Mobitex\Sender::create($username, md5($password), $fromName);
$value = $sender->checkBallance();

printf("You have %.2f PLN left \n", $value);
```

Verify Phone Number 
-------------------

```php
<?php 

$sender = \Mobitex\Sender::create($username, md5($password), $fromName);
try {
  if (false === $sender->verifyNumber("500 100 10"))
  {
    echo "This number is invalid\n";
  } else {
    // …
  }
} catch (Mobitex\Exception $e) {
  // there still can be exceptions, invalid credentials for instance
  echo $e->getMessage() . "\n";
}
```
