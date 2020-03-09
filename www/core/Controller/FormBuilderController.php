<?php

namespace Core\Controller;

class FormBuilderController
{

    private string $html;

    private string $button;

    private bool $first_time = true;

    public function __construct(string $title)
    {
        $this->build($title);
    }

    private function build(string $title): void
    {
        $this->html = <<<HTML
            <form class="m-4 p-3 border rounded" action="" method="post">
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
                <div class="form-group {$class}">\n
            HTML;
        } else {
            $this->html .= <<<HTML
                </div>\n
                <div class="form-group {$class}">\n
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
                    <input class="form-control" type="{$type}" name="{$id}" id="{$id}" $attr>\n
        HTML;
    }

    public function addSelect(string $id, string $label, array $options): void
    {
        $option = '';
        foreach ($options['options'] as $key => $value) {
            $selected = ($options['selected'] == $value) ? 'selected' : '';
            $option .= <<<HTML
                            <option value="{$value}" {$selected}>{$value}</option>\n
            HTML;
        }
        $this->html .= <<<HTML
                    <label for="{$id}">{$label}</label>\n
                    <select class="custom-select" name="{$id}" id="{$id}">
                        {$option}
                    </select>\n
        HTML;
    }

    public function addCheckbox(string $id, string $value, string $label): void
    {
        if ($value === "true" || $value === true) {
            $checked = 'checked=""';
        }
        $this->html .= <<<HTML
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="{$id}" value="false">
                        <input type="checkbox" class="custom-control-input" id="{$id}" name="{$id}" value="true" {$checked}>
                        <label class="custom-control-label" for="{$id}">{$label}</label>
                    </div>\n
        HTML;
    }

    public function addSubmit(string $id, string $value, string $class): void
    {
        $this->button = <<<HTML
        <button type="submit" id="{$id}" class="{$class}">{$value}</button>\n
        HTML;
    }

    public function addToken(string $token): void
    {
        $this->html .= <<<HTML
        <input type="hidden" id="{$token}" name="token" value="{$token}">\n
        HTML;
    }

    public function getForm(): string
    {
        $this->html .= <<<HTML
                        </div><!-- end of form-group -->\n
                    </div><!-- end of row -->\n
                </fieldset>\n
                {$this->button}
            </form>\n
        HTML;
        return (string) $this->html;
    }
}
