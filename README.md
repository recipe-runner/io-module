# IO module

[![Latest Version on Packagist](https://img.shields.io/packagist/v/recipe-runner/io-module.svg?style=flat-square)](https://packagist.org/packages/recipe-runner/io-module)
[![Build Status](https://img.shields.io/travis/recipe-runner/io-module/master.svg?style=flat-square)](https://travis-ci.org/recipe-runner/io-module)

Input/output operations such as `write` or `ask` for interacting with the user.

## Requires

* PHP +7.2
* [Recipe Runner](https://github.com/recipe-runner/recipe-runner)

## Installation

The preferred installation method is [composer](https://getcomposer.org):

```bash
composer require recipe-runner/io-module
```

## Usage

### Method: `write`

Write a message to the output.

```yaml
steps:
    - actions:
        - write: "Hi user. Welcome back."
```

Messages with several lines are allowed:

```yaml
steps:
    - actions:
        - write: 
            "Hi user"
            "Welcome :)"
```

### Method: `ask`

Ask a question to the user.

```yaml
steps:
    - actions:
        - ask: "What's your name?"
```

Default value for a question is empty string. Set a custom default value is possible:

```yaml
ask:
  question: "What's your name?"
  default: "Jack"
```

Response:

```json
{
  "response": "bla bla"
}
```

### Method `ask_yes_no`

Ask a yes/no question to the user.
Values accepted as response:

* `true`: true, "true", "yes", "1", 1
* `false`: false, "false", "no", "0", 0

Default value: `true`.

```yaml
ask_yes_no: "Are you sure?"
```
or

```yaml
ask_yes_no:
  question: "What's your name?"
  default: true
```
Response:

```json
{
  "response": true # boolean value
}
```

## Unit tests

You can run the unit tests with the following command:

```bash
$ cd io-module
$ composer test
```

## License

This library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
