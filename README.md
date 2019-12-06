# TechDivision.Form.Encryption
This package gives you a very basic PGP encryption for the [neos/form-builder](https://github.com/neos/form-builder) package.  

## Simple Setup
This enables a basic encryption with least configuration. 
You basically just need a public key.

1) Install the package using `composer require techdivision/form-encryption`.  
If not yet installed, it will also install the Neos Formbuilder.  
2) Add your own pgp PUBLIC key (not as ascii, but as binary file) `gpg --dearmor < yourPublicKey.asc > yourPublicKey.gpg`  
*Never add your private key!*  
3) Add the path to your key as well as the email your key is attributed with to configuration (do not use `resource://` links here as they wont work)  
```
TechDivision:
  Form:
    Encryption:
      gpg:
        options:
          gpgArguments:
            '--keyring': '%FLOW_PATH_PACKAGES%Application/TechDivision.Form.Encryption/Resources/Public/Keys/yourPublicKey.gpg'
            '--recipient': 'you@domain.com'
```  
4) Add the path to your gpg binary and the homedir attribute.  
```
TechDivision:
  Form:
    Encryption:
      gpg:
        options:
          gpgBinary: '/usr/bin/gpg'
          gpgArguments:
          '--homedir': '~/.gnupg'
```  
5) Add the `EncryptedEmailFinisher` to your form - either in fusion or as a NodeType.  
Remove any other EmailFinisher from the form.


## Advanced setup
If you are familiar with gpg and want to set it up on your own, you can easily change all the arguments you want. 
This is great if you can add your keys directly in gpg, check them against a trust db etc.  

## FAQs
- Why did we choose an exec command over php gnupg extension?  
gpg is widely spread across many *nix distributions. Enabling a simple and straightforward usage was more importand than known but limited drawbacks (key validation, signing etc.).  
- Why didnt we use the SwiftMailer Event Dispatcher?  
We tried to, but it would have required more Classes to be overwritte.
- Why didnt we use the SwiftMailer Signer Pattern?  
Because signing does need private keys, which we wanted to avoid in order to have a simple usage.  

### Contribution
We will be happy to receive pull requests - dont hesitate!