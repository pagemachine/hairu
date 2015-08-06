# Hairu [![Build Status](https://travis-ci.org/pagemachine/hairu.svg)](https://travis-ci.org/pagemachine/hairu)

Flexible login/logout form based on Extbase/Fluid to replace the *felogin* extension shipped with TYPO3 CMS.

入る (*hairu*, Japanese) means "enter"

## Installation

This extension is installable from various sources:

1. From the [TYPO3 Extension Repository](typo3.org/extensions/repository/view/hairu)
2. From [Github](https://github.com/pagemachine/hairu/releases)
3. Via [Composer](https://packagist.org/packages/pagemachine/hairu):
        
        composer require pagemachine/hairu

After installation a new content element *Authentication form* will be available in the *Form elements* section. Make sure to also include the static template.

## Configuration

After including the static template a few options will be available in the Template Constant Editor for customization.

Make sure you set at least the *Default storage PID* to the page where your frontend user records are stored.

You can also use the `stdWrap` property on any `settings` value for custom processing. Example for easy translation of the password reset mail subject:

    plugin.tx_hairu {
      settings {
        passwordReset {
          mail {
            subject.stdWrap.data = LLL:.../locallang.xlf:passwordReset.mail.subject
          }
        }
      }
    }

## Password reset validation

The validation rules applied within the password reset process can be customized freely through TypoScript. Example from the default configuration:

    plugin.tx_hairu {
      // ...
      mvc.validation {
        // Validation of Authentication controller action arguments
        Authentication {
          // ...
          completePasswordReset {
            password {
              1 {
                type = StringLength
                options {
                  minimum = 5
                }
              }
            }
          }
        }
      }
    }

You can use any validator type as long as Extbase can resolve it. In the example the builtin `StringLengthValidator` is set with a minimum length of 5.

The following formats for the validator type are supported:

* Extbase builtin validators: `ValidatorType`
* Fully qualified class name: `Vendor\Package\Validation\Validator\CustomValidator`
* Shorthand syntax: `Vendor.Package:CustomValidator` (internally resolved to the fully qualified class name)

## Issues

Found a bug? Need a feature? Let us know through our [issue tracker](https://github.com/pagemachine/hairu/issues).
