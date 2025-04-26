<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class JobFilterService
{
    protected Builder $query;
    protected array $parsed = [];

    /**
     * Initialize the filter service with a base query.
     */
    public function __construct()
    {
        $this->query = Job::query();
    }

    /**
     * Apply the filter parameters and return the filtered query.
     */
    public function filter(?string $filterString = null): Builder
    {
        if (empty($filterString)) {
            return $this->query;
        }

        // Parse the filter string
        $this->parsed = $this->parseFilterString($filterString);
        
        // Apply the parsed filter to the query
        $this->applyFilter($this->parsed);

        return $this->query;
    }

    /**
     * Parse the filter string into a structured array.
     */
    protected function parseFilterString(string $filterString): array
    {
        // Basic parsing for AND/OR operators and grouping
        // This is a simplified implementation
        
        // First, handle parentheses for grouping
        $groups = [];
        $currentGroup = '';
        $depth = 0;
        
        for ($i = 0; $i < strlen($filterString); $i++) {
            $char = $filterString[$i];
            
            if ($char === '(') {
                $depth++;
                if ($depth === 1) {
                    continue; // Skip the outermost opening parenthesis
                }
            } elseif ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    continue; // Skip the outermost closing parenthesis
                }
            }
            
            if ($depth > 0 || !in_array($char, [' '])) {
                $currentGroup .= $char;
            }
            
            // When we reach a logical operator or the end, save the current group
            if (($depth === 0 && ($char === ' ' || $i === strlen($filterString) - 1)) || $i === strlen($filterString) - 1) {
                if (!empty($currentGroup)) {
                    $groups[] = trim($currentGroup);
                    $currentGroup = '';
                }
            }
        }
        
        // Now parse the conditions from groups
        $result = [];
        $currentOperator = null;
        
        foreach ($groups as $group) {
            if (strtoupper($group) === 'AND') {
                $currentOperator = 'AND';
                continue;
            } elseif (strtoupper($group) === 'OR') {
                $currentOperator = 'OR';
                continue;
            }
            
            // Handle nested groups recursively
            if (Str::startsWith($group, '(') && Str::endsWith($group, ')')) {
                $nestedFilter = substr($group, 1, -1);
                $condition = [
                    'type' => 'group',
                    'conditions' => $this->parseFilterString($nestedFilter)
                ];
            } else {
                // Handle individual conditions
                $condition = $this->parseCondition($group);
            }
            
            if ($currentOperator) {
                $result[] = [
                    'type' => 'operator',
                    'value' => $currentOperator
                ];
            }
            
            $result[] = $condition;
            $currentOperator = null;
        }
        
        return $result;
    }

    /**
     * Parse an individual condition into a structured array.
     */
    protected function parseCondition(string $condition): array
    {
        // Split by operator
        $operators = ['>=', '<=', '!=', '=', '>', '<', 'LIKE', 'HAS_ANY', 'IS_ANY', 'EXISTS', 'IN'];
        $operator = null;
        $field = null;
        $value = null;
        
        foreach ($operators as $op) {
            if (stripos($condition, $op) !== false) {
                $parts = explode($op, $condition, 2);
                $field = trim($parts[0]);    // ... [previous code as shown above] ...

                $value = trim($parts[1]);
                $operator = $op;
                break;
            }
        }
        
        // If it's an attribute filter (prefixed with 'attribute:')
        $isAttribute = false;
        if (Str::startsWith($field, 'attribute:')) {
            $isAttribute = true;
            $field = Str::after($field, 'attribute:');
        }
        
        // Process relationship filters
        $isRelationship = false;
        $relationshipType = null;
        
        if (in_array($field, ['languages', 'locations', 'categories'])) {
            $isRelationship = true;
            $relationshipType = $field;
        }
        
        // Process values for IN, HAS_ANY, IS_ANY operators
        if (in_array($operator, ['IN', 'HAS_ANY', 'IS_ANY'])) {
            // Remove parentheses and split by comma
            $value = str_replace(['(', ')'], '', $value);
            $value = array_map('trim', explode(',', $value));
        }
        
        return [
            'type' => 'condition',
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'is_attribute' => $isAttribute,
            'is_relationship' => $isRelationship,
            'relationship_type' => $relationshipType
        ];
    }

    /**
     * Apply the parsed filter to the query.
     */
    protected function applyFilter(array $parsed, string $method = 'where'): void
    {
        $lastWasOperator = false;
        $currentMethod = $method;

        foreach ($parsed as $part) {
            if ($part['type'] === 'operator') {
                $currentMethod = strtolower($part['value'] === 'AND' ? 'where' : 'orWhere');
                $lastWasOperator = true;
                continue;
            }
            
            if ($part['type'] === 'group') {
                $this->query->$currentMethod(function ($query) use ($part, $currentMethod) {
                    $subFilterService = new self();
                    $subFilterService->query = $query;
                    $subFilterService->applyFilter($part['conditions'], $currentMethod === 'where' ? 'where' : 'orWhere');
                });
                $lastWasOperator = false;
                continue;
            }
            
            // Handle condition
            $field = $part['field'];
            $operator = $part['operator'];
            $value = $part['value'];
            
            // If this is an attribute filter
            if ($part['is_attribute']) {
                $this->applyAttributeFilter($field, $operator, $value, $currentMethod);
            }
            // If this is a relationship filter
            elseif ($part['is_relationship']) {
                $this->applyRelationshipFilter($part['relationship_type'], $operator, $value, $currentMethod);
            }
            // Regular field filter
            else {
                $this->applyFieldFilter($field, $operator, $value, $currentMethod);
            }
            
            $lastWasOperator = false;
        }
    }

    /**
     * Apply a filter for a standard field.
     */
    protected function applyFieldFilter(string $field, string $operator, $value, string $method): void
    {
        // Convert the operator from our API format to Eloquent format
        $sqlOperator = $this->mapOperator($operator);
        
        if ($operator === 'IN') {
            $this->query->$method(function ($query) use ($field, $value) {
                $query->whereIn($field, $value);
            });
        } elseif ($operator === 'LIKE') {
            $this->query->$method($field, 'LIKE', "%{$value}%");
        } else {
            $this->query->$method($field, $sqlOperator, $value);
        }
    }

    /**
     * Apply a filter for an attribute (EAV).
     */
    protected function applyAttributeFilter(string $attributeName, string $operator, $value, string $method): void
    {
        $attribute = Attribute::where('name', $attributeName)->first();
        
        if (!$attribute) {
            return;
        }
        
        $attributeId = $attribute->id;
        $sqlOperator = $this->mapOperator($operator);
        
        $this->query->$method(function ($query) use ($attributeId, $sqlOperator, $value, $attribute, $operator) {
            $query->whereHas('attributeValues', function ($subQuery) use ($attributeId, $sqlOperator, $value, $attribute, $operator) {
                $subQuery->where('attribute_id', $attributeId);
                
                // Handle different attribute types
                switch ($attribute->type) {
                    case Attribute::TYPE_BOOLEAN:
                        $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                        $subQuery->where('value', $boolValue);
                        break;
                    
                    case Attribute::TYPE_SELECT:
                        if ($operator === 'IN') {
                            $subQuery->whereIn('value', $value);
                        } else {
                            $subQuery->where('value', $sqlOperator, $value);
                        }
                        break;
                    
                    case Attribute::TYPE_DATE:
                        $dateValue = date('Y-m-d', strtotime($value));
                        $subQuery->where('value', $sqlOperator, $dateValue);
                        break;
                    
                    case Attribute::TYPE_NUMBER:
                        // Cast the string value in the database to a number for comparison
                        $subQuery->whereRaw("CAST(value AS DECIMAL) {$sqlOperator} ?", [(float) $value]);
                        break;
                    
                    default: // TEXT
                        if ($operator === 'LIKE') {
                            $subQuery->where('value', 'LIKE', "%{$value}%");
                        } else {
                            $subQuery->where('value', $sqlOperator, $value);
                        }
                }
            });
        });
    }

    /**
     * Apply a filter for a relationship.
     */
    protected function applyRelationshipFilter(string $relationshipType, string $operator, $value, string $method): void
    {
        switch ($operator) {
            case '=': // Exact match (single value)
                $this->query->$method(function ($query) use ($relationshipType, $value) {
                    $query->whereHas($relationshipType, function ($subQuery) use ($value, $relationshipType) {
                        if ($relationshipType === 'locations') {
                            $subQuery->where('city', $value);
                        } else {
                            $subQuery->where('name', $value);
                        }
                    });
                });
                break;
                
            case 'HAS_ANY': // Job has any of the specified values
                $this->query->$method(function ($query) use ($relationshipType, $value) {
                    $query->whereHas($relationshipType, function ($subQuery) use ($value, $relationshipType) {
                        if ($relationshipType === 'locations') {
                            $subQuery->whereIn('city', $value);
                        } else {
                            $subQuery->whereIn('name', $value);
                        }
                    });
                });
                break;
                
            case 'IS_ANY': // Relationship matches any of the values
                $this->query->$method(function ($query) use ($relationshipType, $value) {
                    foreach ($value as $index => $val) {
                        $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                        $query->$method($relationshipType, function ($subQuery) use ($val, $relationshipType) {
                            if ($relationshipType === 'locations') {
                                $subQuery->where('city', $val);
                            } else {
                                $subQuery->where('name', $val);
                            }
                        });
                    }
                });
                break;
                
            case 'EXISTS': // Relationship exists
                $this->query->$method(function ($query) use ($relationshipType) {
                    $query->has($relationshipType);
                });
                break;
        }
    }

    /**
     * Map API operators to SQL operators.
     */
    protected function mapOperator(string $operator): string
    {
        $operatorMap = [
            '=' => '=',
            '!=' => '!=',
            '>' => '>',
            '<' => '<',
            '>=' => '>=',
            '<=' => '<=',
            'LIKE' => 'LIKE',
        ];

        return $operatorMap[$operator] ?? '=';
    }
}