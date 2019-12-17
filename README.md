# Treestring (`Warning development discontinued`)
PHP string obfuscator / login security enhancement

## About 
I created treestring to enable php developers securing their web applications using just some simple php calls. This should takle the weakness of using simple md5 or similar hashes.

## How it works
If you create/generate a new treestring representation the class wil generate a codeblock (length and strength depending on the secStrength param you define).

Than it will manipulate your password using the generic codeblock, encrypt the codeblock with aes256 using your password and return and md5hash of the manipulated string.

This string can be stored in the database. If you use the parse/authentication method, it will decrypt the treestring generated file, maniupulate your password and return it. This representation can be compared to the database stored hash.

Through this method, an simple sql-injection can not (or very very very unlikely) lead to an gaining of priviliged user accounts. If the hacker would crack the database stored hash he only would retrieve an maximum obfuscated representation of the password, but never the password itself. If the hacker would only gain file access (i.e through an local file inclusion) he would only gain the aes256 encrypted file, wich is encrypted using the password. To gain the content the hacker would have to crack the password.

This class may be breakable through timing attacks in combination with birth-replay attack, but this is very unlikely for the most "Hackers" out there since it would need a big amount of effort put into it. Its not meant to protect high security environments, but to help all "normal" webapplications out there.

For usage info check the following:

sincerely yours, voodooEntity

## Simple usage, just include the Treestring class to your application and use as in the following examples    
### The first example shows how to create a new user treestring representation    
```php
// first we include the class
include('Treestring.php');

// than we define a storage directory this is needed
// for the Treestring class to store encrypted files
$storageDirectory = "./storage/treestring/";

// now we define a security strength. its optional, if not passed
// the value would be 15. The higher the number the stronger the 
// class will protect your logins. Dont go to high tho ^^
$secStrength = 20;

// now we init the treestring class with the defined params
$treeString = new Treestring($storageDirectory, $secStrength);

// now we create the users treestring representation 
// important the username has to be unique. if you dont have
// unique usernames use the email instead, it just has to be an
// unique identifier 
$hashToStoreInDatabase = $treeString->generate($password,$identifier)

// finally you store the $hashToStoreInDatabase in your database
// corrosponding to the user
```

## The second example to show how to use treestring to authenticate an existing user     
```php
// first we include the class
include('Treestring.php');

// than we define a storage directory this is needed
// for the Treestring class to store encrypted files
$storageDirectory = "./storage/treestring/";

// now we define a security strength. its optional, if not passed
// the value would be 15. The higher the number the stronger the 
// class will protect your logins. Dont go to high tho ^^
$secStrength = 20;

// now we init the treestring class with the defined params
$treeString = new Treestring($storageDirectory, $secStrength);

// now we create the users treestring representation 
// important the username has to be unique. if you dont have
// unique usernames use the email instead, it just has to be an
// unique identifier 
$hashToCampareWithDatabase = $treeString->parse($password,$identifier)

// now you use the $hashToCompareWithDatabase to compare the value
// with the stored in the db one. If it fits, the user is authed fine.

```
