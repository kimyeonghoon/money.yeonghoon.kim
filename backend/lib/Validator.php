<?php

class Validator {
    private $errors = [];

    public function required($value, $fieldName) {
        if (empty($value) && $value !== '0') {
            $this->errors[$fieldName] = "{$fieldName}는 필수 입력 항목입니다.";
        }
        return $this;
    }

    public function numeric($value, $fieldName) {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$fieldName] = "{$fieldName}는 숫자여야 합니다.";
        }
        return $this;
    }

    public function amount($value, $fieldName) {
        if (!empty($value)) {
            if (!is_numeric($value)) {
                $this->errors[$fieldName] = "{$fieldName}는 숫자여야 합니다.";
            } else {
                $intValue = (int)$value;
                if ($intValue < 0) {
                    $this->errors[$fieldName] = "{$fieldName}는 0 이상이어야 합니다.";
                }
                if ($intValue > 2147483647) { // INT 최대값
                    $this->errors[$fieldName] = "{$fieldName}가 너무 큽니다.";
                }
            }
        }
        return $this;
    }

    public function expenseAmount($value, $fieldName) {
        if (isset($value) && $value !== '' && $value !== null) {
            if (!is_numeric($value)) {
                $this->errors[$fieldName] = "{$fieldName}는 숫자여야 합니다.";
            } else {
                $intValue = (int)$value;
                if ($intValue < 0) {
                    $this->errors[$fieldName] = "{$fieldName}는 0 이상이어야 합니다.";
                }
                if ($intValue > 2147483647) { // INT 최대값
                    $this->errors[$fieldName] = "{$fieldName}가 너무 큽니다.";
                }
            }
        }
        return $this;
    }

    public function maxLength($value, $fieldName, $max) {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$fieldName] = "{$fieldName}는 최대 {$max}자까지 입력 가능합니다.";
        }
        return $this;
    }

    public function inArray($value, $fieldName, $allowedValues) {
        if (!empty($value) && !in_array($value, $allowedValues)) {
            $allowedStr = implode(', ', $allowedValues);
            $this->errors[$fieldName] = "{$fieldName}는 다음 값 중 하나여야 합니다: {$allowedStr}";
        }
        return $this;
    }

    public function date($value, $fieldName) {
        if (!empty($value)) {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->errors[$fieldName] = "{$fieldName}는 YYYY-MM-DD 형식의 올바른 날짜여야 합니다.";
            }
        }
        return $this;
    }

    public function integer($value, $fieldName, $min = null, $max = null) {
        if (!empty($value)) {
            if (!filter_var($value, FILTER_VALIDATE_INT)) {
                $this->errors[$fieldName] = "{$fieldName}는 정수여야 합니다.";
            } else {
                $intValue = (int)$value;
                if ($min !== null && $intValue < $min) {
                    $this->errors[$fieldName] = "{$fieldName}는 {$min} 이상이어야 합니다.";
                }
                if ($max !== null && $intValue > $max) {
                    $this->errors[$fieldName] = "{$fieldName}는 {$max} 이하여야 합니다.";
                }
            }
        }
        return $this;
    }

    public function boolean($value, $fieldName) {
        if (!empty($value) && !in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'])) {
            $this->errors[$fieldName] = "{$fieldName}는 참/거짓 값이어야 합니다.";
        }
        return $this;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    public function clear() {
        $this->errors = [];
        return $this;
    }

    public static function validateCashAsset($data, $isPartial = false) {
        $validator = new self();

        // 부분 업데이트가 아닌 경우 필수 필드 검증
        if (!$isPartial) {
            $validator->required($data['item_name'] ?? '', 'item_name')
                     ->maxLength($data['item_name'] ?? '', 'item_name', 200);

            // balance는 선택사항으로 변경
            if (isset($data['balance']) && !empty($data['balance'])) {
                $validator->amount($data['balance'], 'balance');
            }
        } else {
            // 부분 업데이트인 경우 제공된 필드만 검증
            if (isset($data['item_name'])) {
                $validator->required($data['item_name'], 'item_name')
                         ->maxLength($data['item_name'], 'item_name', 200);
            }

            if (isset($data['balance'])) {
                $validator->required($data['balance'], 'balance')
                         ->amount($data['balance'], 'balance');
            }
        }

        // type이 제공된 경우에만 검증 (기본값이 있으므로)
        if (isset($data['type'])) {
            $validator->inArray($data['type'], 'type', ['현금', '통장']);
        }

        // account_name이 제공된 경우에만 검증
        if (isset($data['account_name']) && !empty($data['account_name'])) {
            $validator->maxLength($data['account_name'], 'account_name', 100);
        }

        return $validator;
    }

    public static function validateCashAssetForBalanceUpdate($data) {
        $validator = new self();

        $validator->required($data['balance'] ?? '', 'balance')
                 ->amount($data['balance'] ?? '', 'balance');

        return $validator;
    }

    public static function validateInvestmentAsset($data) {
        $validator = new self();

        $validator->required($data['category'] ?? '', 'category')
                 ->inArray($data['category'] ?? '', 'category', ['저축', '혼합', '주식']);

        $validator->required($data['item_name'] ?? '', 'item_name')
                 ->maxLength($data['item_name'] ?? '', 'item_name', 200);

        $validator->required($data['current_value'] ?? '', 'current_value')
                 ->amount($data['current_value'] ?? '', 'current_value');

        $validator->required($data['deposit_amount'] ?? '', 'deposit_amount')
                 ->amount($data['deposit_amount'] ?? '', 'deposit_amount');

        if (!empty($data['account_name'])) {
            $validator->maxLength($data['account_name'], 'account_name', 100);
        }

        return $validator;
    }

    public static function validatePensionAsset($data, $isPartial = false) {
        $validator = new self();

        // 부분 업데이트가 아닌 경우 필수 필드 검증
        if (!$isPartial) {
            $validator->required($data['type'] ?? '', 'type')
                     ->inArray($data['type'] ?? '', 'type', ['연금저축', '퇴직연금']);

            $validator->required($data['item_name'] ?? '', 'item_name')
                     ->maxLength($data['item_name'] ?? '', 'item_name', 200);

            $validator->required($data['current_value'] ?? '', 'current_value')
                     ->amount($data['current_value'] ?? '', 'current_value');

            $validator->required($data['deposit_amount'] ?? '', 'deposit_amount')
                     ->amount($data['deposit_amount'] ?? '', 'deposit_amount');
        } else {
            // 부분 업데이트인 경우 제공된 필드만 검증
            if (isset($data['type'])) {
                $validator->required($data['type'], 'type')
                         ->inArray($data['type'], 'type', ['연금저축', '퇴직연금']);
            }

            if (isset($data['item_name'])) {
                $validator->required($data['item_name'], 'item_name')
                         ->maxLength($data['item_name'], 'item_name', 200);
            }

            if (isset($data['current_value'])) {
                $validator->required($data['current_value'], 'current_value')
                         ->amount($data['current_value'], 'current_value');
            }

            if (isset($data['deposit_amount'])) {
                $validator->required($data['deposit_amount'], 'deposit_amount')
                         ->amount($data['deposit_amount'], 'deposit_amount');
            }
        }

        // account_name이 제공된 경우에만 검증
        if (isset($data['account_name']) && !empty($data['account_name'])) {
            $validator->maxLength($data['account_name'], 'account_name', 100);
        }

        return $validator;
    }

    public static function validateDailyExpense($data) {
        $validator = new self();

        $validator->required($data['expense_date'] ?? '', 'expense_date')
                 ->date($data['expense_date'] ?? '', 'expense_date');

        $validator->expenseAmount($data['total_amount'] ?? '', 'total_amount');

        if (!empty($data['food_cost'])) {
            $validator->expenseAmount($data['food_cost'], 'food_cost');
        }
        if (!empty($data['necessities_cost'])) {
            $validator->expenseAmount($data['necessities_cost'], 'necessities_cost');
        }
        if (!empty($data['transportation_cost'])) {
            $validator->expenseAmount($data['transportation_cost'], 'transportation_cost');
        }
        if (!empty($data['other_cost'])) {
            $validator->expenseAmount($data['other_cost'], 'other_cost');
        }

        return $validator;
    }

    public static function validateFixedExpense($data) {
        $validator = new self();

        $validator->required($data['item_name'] ?? '', 'item_name')
                 ->maxLength($data['item_name'] ?? '', 'item_name', 200);

        $validator->required($data['amount'] ?? '', 'amount')
                 ->amount($data['amount'] ?? '', 'amount');

        // payment_date는 선택적 필드 (NULL 허용)
        if (isset($data['payment_date']) && $data['payment_date'] !== null && $data['payment_date'] !== '') {
            $validator->integer($data['payment_date'], 'payment_date', 1, 31);
        }

        $validator->required($data['payment_method'] ?? '', 'payment_method')
                 ->inArray($data['payment_method'] ?? '', 'payment_method', ['신용', '체크', '현금']);

        if (!empty($data['category'])) {
            $validator->maxLength($data['category'], 'category', 50);
        }

        if (isset($data['is_active'])) {
            $validator->boolean($data['is_active'], 'is_active');
        }

        return $validator;
    }

    public static function validatePrepaidExpense($data) {
        $validator = new self();

        $validator->required($data['item_name'] ?? '', 'item_name')
                 ->maxLength($data['item_name'] ?? '', 'item_name', 200);

        $validator->required($data['amount'] ?? '', 'amount')
                 ->amount($data['amount'] ?? '', 'amount');

        $validator->required($data['payment_date'] ?? '', 'payment_date')
                 ->integer($data['payment_date'] ?? '', 'payment_date', 1, 31);

        $validator->required($data['payment_method'] ?? '', 'payment_method')
                 ->inArray($data['payment_method'] ?? '', 'payment_method', ['신용', '체크', '현금']);

        if (!empty($data['expiry_date'])) {
            $validator->date($data['expiry_date'], 'expiry_date');
        }

        if (isset($data['is_active'])) {
            $validator->boolean($data['is_active'], 'is_active');
        }

        return $validator;
    }
}