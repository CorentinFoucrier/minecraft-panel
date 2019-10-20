<?php
namespace Core\Controller;

class FormBuilderController {

    private $html;

    private $button;

    private $first_time = true;

    public function __construct(string $title)
    {
        $this->build($title);
    }

    private function build(string $title): void
    {
        $this->html = <<<HTML
            <form class="mt-4 p-3 border rounded" action="" method="post">
                <fieldset>
                    <p class="h4 ml-2 mt-2">{$title}</p>
                    <div class="row">
        HTML;
    }

    public function addGroup(string $class): void
    {
        if ($this->first_time === true) {
            $this->first_time = false;
            $this->html .= <<<HTML
                <div class="form-group {$class}">
            HTML;
        } else {
            $this->html .= <<<HTML
                </div>
                <div class="form-group {$class}">
            HTML;
        }
    }

    public function addField(string $type, string $id, string $label, array $attributes): void
    {
        $attr = "";
        foreach ($attributes['attributes'] as $key => $value) {
            $attr .= "$key=\"$value\" ";
        }
        $this->html .= <<<HTML
                    <label for="{$id}">{$label}</label>
                    <input class="form-control" type="{$type}" name="{$id}" id="{$id}" $attr>
        HTML;
    }

    public function addSelect(string $id, string $label, array $options): void
    {
        $option = '';
        foreach ($options['options'] as $key => $value) {
            $selected = ($options['selected'] == $value) ? 'selected' : '';
            $option .= <<<HTML
                            <option value="{$value}" {$selected}>{$value}</option>
            HTML;
        }
        $this->html .= <<<HTML
                    <label for="{$id}">{$label}</label>
                    <select class="custom-select" name="{$id}" id="{$id}">
                        {$option}
                    </select>
        HTML;
    }

    public function addSubmit(string $id, string $value, string $class): void
    {
        $this->button .= <<<HTML
        <button type="submit" id="{$id}" class="{$class}">{$value}</button>
        HTML;
    }

    public function getForm(): string
    {
        $this->html .= <<<HTML
                        </div><!-- end of form-group -->
                    </div><!-- end of row -->
                </fieldset>
                {$this->button}
            </form>
        HTML;
        return (string)$this->html;
    }
}