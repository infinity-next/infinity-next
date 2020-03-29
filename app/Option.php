<?php

namespace App;

use App\Contracts\PseudoEnum  as PseudoEnumContract;
use App\Traits\PseudoEnum as PseudoEnum;
use Illuminate\Database\Eloquent\Model;

class Option extends Model implements PseudoEnumContract
{
    use \App\Traits\EloquentBinary;
    use PseudoEnum;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'options';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'option_name';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'option_name',
        'default_value',
        'format',
        'format_parameters',
        'data_type',
        'validation_parameters',
    ];

    /**
     * Psuedo-enum attributes and their permissable values.
     *
     * @var array
     */
    protected $enum = [
        'data_type' => [
            'string',
            'integer',
            'numeric',
            'array',
            'boolean',
            'positive_integer',
            'unsigned_integer',
            'unsigned_numeric',
        ],

        'format' => [
            'textbox',
            'text',
            'spinbox',
            'onoff',
            'onofftextbox',
            'radio',
            'select',
            'checkbox',
            'template',
            'callback',
        ],

        'option_type' => [
            'board',
            'site',
        ],
    ];

    /**
     * Denotes our primary key is not an autoincrementing integer.
     *
     * @var string
     */
    public $incrementing = false;

    /**
     * Determines if Laravel should set created_at and updated_at timestamps.
     *
     * @var array
     */
    public $timestamps = false;

    public function boardSetting()
    {
        return $this->hasOne('\App\BoardSetting', 'option_name');
    }

    public function groups()
    {
        return $this->belongsToMany("\App\OptionGroup", 'option_group_assignments', 'option_name', 'option_group_id');
    }

    public function siteSetting()
    {
        return $this->hasOne('\App\SiteSetting', 'option_name');
    }

    /**
     * Gets choices for this select box.
     *
     * @return array Of value => language associations.
     */
    public function getChoices()
    {
        $formatChoices = $this->getFormatParameter('choices');
        $choices = [];

        foreach ($formatChoices as $choice) {
            $choices[$choice] = trans("config.choices.{$this->option_name}.$choice");
        }

        return $choices;
    }

    /**
     * Return the display name of this option for UIs.
     *
     * @return mixed
     */
    public function getDisplayName()
    {
        return trans("config.option.{$this->option_name}");
    }

    /**
     * Gets our default value and unwraps it from any stream wrappers.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getDefaultValueAttribute($value)
    {
        return binary_unsql($value);
    }

    /**
     * Returns the value that should be applied to the config.
     * Relies on setting data being present.
     *
     * @return mixed
     */
    public function getDisplayValue()
    {
        if (isset($this->attributes['option_value'])) {
            $value = $this->option_value;

            switch ($this->data_type) {
                case 'unsigned_integer':
                    $value = abs((int) $this->option_value);
                    break;

                case 'integer':
                    $value = (int) $this->option_value;
                    break;

                case 'boolean':
                    $value = (bool) $value;
                    break;
            }

            return $value;
        }

        return $this->default_value;
    }

    /**
     * Gets our option value and unwraps it from any stream wrappers.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getOptionValueAttribute($value)
    {
        $value = binary_unsql($value);

        if ($this->attributes['data_type'] == 'array') {
            $value = json_decode($value, true);
        }

        return $value;
    }

    protected $decoded_format_parameters;

    public function getFormatParameter($parameter)
    {
        $parameters = $this->getFormatParameters();

        if (isset($parameters[$parameter])) {
            return $parameters[$parameter];
        }

        return;
    }

    public function getFormatParameters()
    {
        if (!isset($this->decoded_format_parameters)) {
            $this->decoded_format_parameters = json_decode($this->format_parameters, true);
        }

        return $this->decoded_format_parameters;
    }

    public function getSanitaryInput($input)
    {
        switch ($this->data_type) {
            case 'boolean':
                $input = (bool) $input;
                break;
        }

        return $input;
    }

    /**
     * Gets the view path of the option's template for the config panel.
     *
     * @param \App\Http\Controller|descendant $controller
     *
     * @return string
     */
    public function getTemplate($controller)
    {
        switch ($this->format) {
            case 'template':
                return "widgets.config.option.template.{$this->option_name}";
            case 'callback':
                //# TODO ##
                return 'Callback not supported.';
            default:
                return "widgets.config.option.{$this->format}";
        }
    }

    public function getValidation()
    {
        $requirements = [];
        $requirement = '';

        switch ($this->data_type) {
            case 'unsigned_integer':
                $requirement = 'integer|min:0';
                break;

            case 'integer':
                $requirement = 'integer';
                break;

            case 'boolean':
                $requirement = 'boolean';
                break;

            case 'array':
                $requirement = 'array';
                break;
        }

        switch ($this->option_name) {
            case 'boardWordFilter':
                $requirements['boardWordFilter.find'] = [
                    'array',
                    'between:0,50',
                ];
                $requirements['boardWordFilter.replace'] = [
                    'array',
                    'between:0,50',
                ];

                //# TODO ##
                // For Larael 5.2, replace this with the * rule.
                for ($i = 0; $i <= 50; ++$i) {
                    $requirements["boardWordFilter.find.{$i}"] = [
                        'nullable',
                        'string',
                        'between:1,256',
                    ];
                    $requirements["boardWordFilter.replace.{$i}"] = [
                        'nullable',
                        'string',
                        'between:0,256',
                    ];
                }

                break;
        }

        $validation = $this->validation_parameters;
        $parameters = $this->getFormatParameters();

        if (is_array($parameters)) {
            // Replaces $etc vars in the validation parameter of an option with their true values.
            $validation = preg_replace_callback('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', function ($match) use ($parameters) {
                if (isset($parameters[$match[1]])) {
                    // If the value is an array, we want to make that a CSV.
                    if (is_array($parameters[$match[1]])) {
                        return implode(',', $parameters[$match[1]]);
                    }

                    return $parameters[$match[1]];
                }

                return $match[0];
            }, $validation);
        }

        if ($validation != '') {
            if ($requirement != '') {
                $requirement .= '|'.$validation;
            } else {
                $requirement = $validation;
            }
        }

        $requirements[$this->option_name] = $requirement;

        return $requirements;
    }

    /**
     * Is this setting locked? (Editable only by users with special permissions).
     *
     * @return bool
     */
    public function isLocked()
    {
        if (isset($this->attributes['is_locked'])) {
            return (bool) $this->attributes['is_locked'];
        }

        return (bool) $this->is_locked;
    }

    /**
     * Sets our default value and encodes it if required.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setDefaultValueAttribute($value)
    {
        $this->attributes['default_value'] = binary_sql($value);
    }

    /**
     * Sets our option value and encodes it if required.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setOptionValueAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['option_value'] = binary_sql($value);
    }

    /**
     * Joins board_setting data to the options.
     *
     * @param \App\Board $board
     *
     * @return Builder
     */
    public function scopeAndBoardSettings($query, Board $board)
    {
        $query->where('option_type', 'board');

        $query->leftJoin('board_settings', function ($join) use ($board) {
            $join->on('board_settings.option_name', '=', 'options.option_name');
            $join->where('board_settings.board_uri', '=', $board->board_uri);
        });

        $query->addSelect(
            'options.*',
            'board_settings.option_value as option_value',
            'board_settings.is_locked as is_locked'
        );

        $query->orderBy('display_order', 'asc');

        return $query;
    }
}
