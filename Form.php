<?php


//[
//      'name': 'some_name' (= id)
//      'type': 'my text',
//      'label': 'my label',
//      'description': 'my description',
//]
class Form
{
    private string $name;
    private string $title;
    private string $action;
    private string $method;
    private array $descriptor;
    private array $defaultValues;
    private array $errors;
    private string $message;
    private string $hidden;
    private bool $submitButton;

    private function loadOldValues(): array
    {
        if (isset($_SESSION['old']) && isset($_SESSION['old'][$this->name])) {
            $temp = $_SESSION['old'][$this->name];
            unset($_SESSION['old'][$this->name]);
            return $temp;
        } else {
            return [];
        }
    }

    private function loadErrors(): array
    {
        if (isset($_SESSION['error']) && isset($_SESSION['error'][$this->name])) {
            $temp = $_SESSION['error'][$this->name];
            if (isset($_SESSION['error'][$this->name]['__FORM_MSG'])) {
                $temp['__FORM_MSG'] = $_SESSION['error'][$this->name]['__FORM_MSG'];
            }
            unset($_SESSION['error'][$this->name]);
            return $temp;
        } else {
            return [];
        }
    }

    public function __construct(
        string $name,
        string $action,
        string $method,
        array $descriptor,
        string $title = '',
        bool $hidden = true,
        bool $submitButton = true
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->name = $name;
        $this->title = $title === '' ? $this->name : $title;
        $this->action = $action;
        $this->method = $method;
        $this->descriptor = $descriptor;
        $this->defaultValues = $this->loadOldValues();
        $this->errors = $this->loadErrors();
        $this->hidden = $hidden === true ? 'd-none' : '';

        $this->message = '';
        if (isset($_SESSION['msg']) && isset($_SESSION['msg'][$this->name])) {
            $this->message = $_SESSION['msg'][$this->name];
            unset($_SESSION['msg'][$this->name]);
            $this->hidden = '';
        }

        if (isset($this->errors) && $this->errors != []) {
            $this->hidden = '';
        }

        $this->submitButton = $submitButton;
    }

    private function renderInput(
        string $name,
        string $label,
        string $placeholder,
        string $description,
        string $type = 'text'
    ): string {
        $oldValue = $this->defaultValues[$name] ?? '';
        $error = $this->errors[$name] ?? '';
        return <<<HTML
            <div class="form-group">
                <label for="$name">
                $label    
                </label>
                
                <input 
                id="$name"
                name="$name"
                placeholder="$placeholder"
                type="$type"
                value="$oldValue"
                class="form-control"
                >
                
                <span id="{$name}HelpBlock" class="text-muted">$description</span>
                <div class="text-danger"> $error </div>
            </div>
            HTML;
    }

    private function renderSelect(
        string $name,
        string $label,
        string $description,
        array $options,
        array $optionVals = []
    ): string {
        $oldValue = $this->defaultValues[$name] ?? '';
        $error = $this->errors[$name] ?? '';

        $optionsStr = '';
        for ($i = 0; $i < count($options); ++$i) {
            $option = $options[$i];
            if (isset($optionVals[$i])) {
                $optionVal = $optionVals[$i];
            } else {
                $optionVal = $option;
            }

            $selected = '';
            if ($oldValue === $optionVal) {
                $selected = "selected";
            }

            $optionsStr .= <<<HTML
                <option value="$optionVal" $selected>$option</option>
            HTML;
        }
        return <<<HTML
            <div class="form-group">    
                <label for="$name">$label</label>    
                 <div>    
                    <select 
                    id="$name" 
                    name="$name"
                    class="custom-select" 
                    >    
                    $optionsStr
                    </select>    
                    <div class="text-danger"> $error </div>    
                </div>    
                <span id="{$name}HelpBlock" class="form-text text-muted">$description</span>
            </div> 
            HTML;
    }

    private function renderTable(
        string $name,
        string $label,
        string $description,
        array $content
    ): string {
        $res = '';
        $res .= <<<HTML
                      <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                    HTML;

        foreach (array_keys($content[0]) as $k) {
            $res .= <<<HTML
                <th scope="col"> $k </th>
            HTML;
        }

        $res .= <<<HTML
                </tr>
                </thead>
                <tbody>
                
        HTML;

        foreach ($content as $row) {
            $res .= <<<HTML
                    <tr>
                        <th scope="row">1</th>
                HTML;
            foreach ($row as $k => $v) {
                $res .= <<<HTML
                    <td>$v</td>
                HTML;
            }
            $res .= <<<HTML
                    </tr>
                HTML;
        }
        $res .= <<<HTML
                </tbody>
            </table>
        HTML;

        return $res;
    }

    public function render()
    {
        $msg = $this->errors['__FORM_MSG'] ?? '';
        echo <<< HTML
        <div>
            <form name="$this->name" id="$this->name" action="$this->action" method="$this->method" class="$this->hidden">
                <div class="display-4 text-center">
                    $this->title
                </div>
                <div class="text-warning">
                    $msg
                </div>
                <div class="text-info text-center" style="font-size: 24px">
                    $this->message
                </div>
        HTML;

        foreach ($this->descriptor as $tags) {
            switch ($tags['kind']) {
                case 'input':
                    echo $this->renderInput(
                        $tags['name'],
                        $tags['label'] ?? '',
                        $tags['placeholder'] ?? '',
                        $tags['description'] ?? '',
                        $tags['type'],
                    );
                    break;
                case 'select':
                    echo $this->renderSelect(
                        $tags['name'],
                        $tags['label'] ?? '',
                        $tags['description'] ?? '',
                        $tags['options'] ?? [],
                        $tags['optionVals'] ?? [],
                    );
                    break;
                case 'display':
                    echo $this->renderTable(
                        $tags['name'] ?? '',
                        $tags['label'] ?? '',
                        $tags['description'] ?? '',
                        $tags['content'] ?? [],
                    );
                    break;
            }
        }

        if($this->submitButton)
        echo <<< HTML
            <div class="form-group text-center">
                <button name="form[{$this->name}]" type="submit" class="btn btn-outline-primary">Submit</button>
            </div>
        HTML;
        echo <<< HTML
            </form>
        </div>
        HTML;
    }
}