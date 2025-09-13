<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['group', 'key', 'value', 'value_type', 'description'];

    /**
     * Get all settings organized by group.
     *
     * @return array
     */
    public static function getAllSettings(): array
    {
        $settings = self::all();
        $result = [];

        foreach ($settings as $setting) {
            if (!isset($result[$setting->group])) {
                $result[$setting->group] = [];
            }

            $result[$setting->group][$setting->key] = $setting->getTypedValue();
        }

        return $result;
    }

    /**
     * Get all settings for a specific group.
     *
     * @param string $group
     * @return array
     */
    public static function getGroup(string $group): array
    {
        $settings = self::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getTypedValue();
        }

        return $result;
    }

    /**
     * Get a specific setting value.
     *
     * @param string $group
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $group, string $key, $default = null)
    {
        $setting = self::where('group', $group)
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    /**
     * Set a specific setting value.
     *
     * @param string $group
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return \App\Models\Setting
     */
    public static function set(string $group, string $key, $value, ?string $description = null): Setting
    {
        $valueType = self::determineValueType($value);
        $stringValue = self::valueToString($value, $valueType);
        
        $setting = self::updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value' => $stringValue,
                'value_type' => $valueType,
                'description' => $description,
            ]
        );
        
        return $setting;
    }

    /**
     * Update multiple settings at once.
     *
     * @param array $settings
     * @return void
     */
    public static function updateSettings(array $settings): void
    {
        foreach ($settings as $group => $groupSettings) {
            if (is_array($groupSettings)) {
                foreach ($groupSettings as $key => $value) {
                    self::set($group, $key, $value);
                }
            }
        }
    }

    /**
     * Determine the value type based on the provided value.
     *
     * @param mixed $value
     * @return string
     */
    protected static function determineValueType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value) || is_object($value)) {
            return 'json';
        } else {
            return 'string';
        }
    }

    /**
     * Convert value to string for database storage.
     *
     * @param mixed $value
     * @param string $type
     * @return string|null
     */
    protected static function valueToString($value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }
        
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Get the typed value based on value_type.
     *
     * @return mixed
     */
    public function getTypedValue()
    {
        if ($this->value === null) {
            return null;
        }
        
        switch ($this->value_type) {
            case 'boolean':
                return $this->value === '1' || $this->value === 'true';
            case 'integer':
                return (int) $this->value;
            case 'float':
                return (float) $this->value;
            case 'json':
                return json_decode($this->value, true);
            default:
                return $this->value;
        }
    }
}