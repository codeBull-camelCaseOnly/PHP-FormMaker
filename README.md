# PHP-FormMaker

A simple library that helps in form rendering and validation.

## Usage

The library consists of two files - `Form.php` and `FormBackend.php`, first one is for rendering the form in the frontend and second one is for validating the form in the backend.

### Frontend

The frontend uses bootstrap to display forms. So, it is necessary to include bootstrap (4+ recommended) in your `<head>` section.

Include the `Form.php` file in your project and initialize an object of the Form class. The constructor of the Form class has the following arguments: -

```php
public function __construct(
        string $name,
        string $action,
        string $method,
        array $descriptor,
        string $title = '',
        bool $hidden = true,
        bool $submitButton = true
    ) 
```

- `string $name`: represents the name of the form. It is used to identify the form uniquely. It is also used to fetch the form's flash sessions. Moreover, the `form` tag is given this value as its `name` and `id` attributes during rendering.

- `string $action`: This is the URL where the form should be redirected to after submit button is pressed.

- `string $method`: The method to be used to submit the form. Example - 'GET', 'POST'.

- `array $descriptor`: This is an array of array which describes the various inputs inside the form. Each input is described by its own array. It follows a specific format for various kinds of input tags: -

    - For inputs made of 'input' tags: -

      ```php
      [
          'kind' => 'input'		
          'name' => ...				// name attribute for the input tag
          'label' => ...				// label to be assigned to the input tag
          'description' => ...		// description for the input tag
          'placeholder' => ...		// placeholder for the input tag
          'type' => ...				// 'text', 'number', 'password', etc...
      ]
      ```

      For example: -

      ```php
       [
           'kind' => 'input',
           'name' => 'name',
           'label' => 'user\'s name',
           'description' => 'Write the username for your account.',
           'placeholder' => 'username',
           'type' => 'text'
       ],
      ```

    - For input made of 'select' tags: -

      ```php
      [
          'kind' => 'select',
          'name' => ...				// name attribute for the input tag
          'label' => ...				// label to be assigned to the input tag
          'description' => ...		// description for the input tag
          'options' => [...]			// array of string depicting options to be displayed
          'optionVals' => [...]		// values for the options.
      ],
      ```

      For example: -

      ```php
      [
          'kind' => 'select',
          'name' => 'gender',
          'label' => 'Gender',
          'description' => 'Select your gender.',
          'options' => ['Male', 'Female', 'Other'],
          'optionVals' => [0, 1, 2]
      ],
      ```

  *Currently, supports only these two kinds of inputs, will add more later*

  Thus, an example of descriptor array would be: -

  ```php
  [
      [
          'kind' => 'input',
          'name' => 'name',
          'label' => 'user\'s name',
          'description' => 'Write the username for your account.',
          'placeholder' => 'username',
          'type' => 'text'
      ],
      [
          'kind' => 'input',
          'name' => 'password',
          'label' => 'user\'s password',
          'description' => 'Write the password for your account.',
          'placeholder' => '********',
          'type' => 'password'
      ],
      [
          'kind' => 'select',
          'name' => 'gender',
          'label' => 'Gender',
          'description' => 'Select your gender.',
          'options' => ['Male', 'Female', 'Other'],
          'optionVals' => [0, 1, 2]
      ], 
  ]
  ```

  Note that the inputs would be rendered from top to bottom order of the array.

- `string $title = ''`: Depicts the title of the form which is displayed above the form
- `bool $hidden = true`: Whether to hide form or not. By default, form has a class of "d-none". Which is `display:none` style in bootstrap. Note: If form has an error, or message to display, it will be automatically be unhidden.
- `bool $submitButton = true`: Whether to add submit button or not.

After initializing a Form class instance. You can display the form in the frontend using the `render()` method.

### Backend

The backend is just a class `FormBackend` which has following constructor signature: -

```php
 public function __construct(
        string $name,
        array $descriptor,
        string $method,
        string $originUrl,
        bool $validateEmpty = true
    )
```

- `$name`, `$descriptor`, and `$method`: must be the same as the frontend form.
- `string $originUrl`: It is the URL where the form's frontend resides. It is used to redirect to the form in case of errors or messages.
- `bool $validateEmpty = true`:  If set to true, it automatically validates and returns error for empty input values.

After initializing new instance for the `FormBackend` class, you can call the following methods: -

- `addError(string $input, string $error)`: Add an error message to display below the input in the form frontend. It stores the error messages and waits for a redirect command to the form's frontend page.
- `setErrorMessage(string $msg)`: Sets a `text-warning` message to be displayed above the form as warning. Waits for a redirect command.
- `redirectWithMessage(string $msg)`: Sets a `text-info` message and redirects to the form's frontend.
- `addValidator(string $input, callable $validator)`: Adds a validator function to a input. The particular input's value would be validated against the function. The `$validator` function must have a signature - `($input, $value)`. The `$input` would be the name of the `$input` and `$value` would be its value. This method does not automatically validate the input, it just adds the validator in the list of validators.
- `redirectIfAnythingWrong()`: Redirects if any validators have failed or any error has been discovered. Generally, no need to call this explicitly.
- `validate()`: Validates the inputs against the list of validator functions defined for the inputs. If any validator fails, the `addError` method is called and the error for that input is stored, finally `redirectIfAnythingWrong` method is called.
- `getValues()`: get the values of inputs in an associative array in the following format - `['name of input' => value, ...]`.