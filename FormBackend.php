<?php

class FormBackend
{
    private string $name;
    private string $method;
    private array $descriptor;
    private string $originUrl;
    private array $values;
    private array $validators;

    public function __construct(
        string $name,
        array $descriptor,
        string $method,
        string $originUrl,
        bool $validateEmpty = true
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->name = $name;
        $this->descriptor = $descriptor;
        $this->method = $method;
        $this->originUrl = $originUrl;

        $values = $this->method === 'GET' ? $_GET : $_POST;
        foreach ($descriptor as $inputs) {
            $name = $inputs['name'];
            if ($validateEmpty) {
                $this->addValidator(
                    $name,
                    function ($key, $value) {
//                        dump($key, $value);
                        if (!isset($value) || $value == "") {
                            return $key . ' cannot be empty';
                        }
                        return true;
                    }
                );
            }
            if (isset($values[$name])) {
                $this->values[$name] = htmlspecialchars($values[$name]);
            }
        }
    }

    public function addError(string $input, string $error)
    {
        if (!isset($_SESSION['error'])) {
            $_SESSION['error'] = [];
        }
        if (!isset($_SESSION['error'][$this->name])) {
            $_SESSION['error'][$this->name] = [];
        }
        if (!isset($_SESSION['error'][$this->name][$input])) {
            $_SESSION['error'][$this->name][$input] = $error;
        }
    }

    public function setErrorMessage(string $msg)
    {
        if (!isset($_SESSION['error'])) {
            $_SESSION['error'] = [];
        }
        if (!isset($_SESSION['error'][$this->name])) {
            $_SESSION['error'][$this->name] = [];
        }
        $_SESSION['error'][$this->name]['__FORM_MSG'] = $msg;
    }

    public function redirectWithMessage(string $msg)
    {
        if (!isset($_SESSION['msg'])) {
            $_SESSION['msg'] = [];
        }
        if (!isset($_SESSION['msg'][$this->name])) {
            $_SESSION['msg'][$this->name] = $msg;
        }
        header('Location: ' . $this->originUrl);
        exit();
    }

    public function addValidator(string $input, callable $validator)
    {
        $this->validators[] = ['for' => $input, 'function' => $validator];
    }

    public function redirectIfAnythingWrong()
    {
        if (isset($_SESSION['error'][$this->name]) && $_SESSION['error'][$this->name] != []) {
            if (!isset($_SESSION['old'])) {
                $_SESSION['old'] = [];
            }
            $_SESSION['old'][$this->name] = $this->values;
            header('Location: ' . $this->originUrl);
            exit();
        }
    }

    public function validate()
    {
        foreach ($this->validators as $validator) {
            /**
             * @var string $for
             * @var string $function
             */
            extract($validator);
            $error = $function($for, $this->values[$for]);
            if ($error !== true) {
                $this->addError($for, $error);
            }
        }
        $this->redirectIfAnythingWrong();
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }
}