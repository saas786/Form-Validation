<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Yusuf Koç
 * Date: 07.09.2011
 * Time: 14:23
 * License: GNU General Public License, version 2.
 */
 
class Form_Validation 
{
    /**
     * POST ve GET değerini içerir.
     * @var array
     */
    private $var;

    /**
     * Rolleri Barındırır.
     * @var array
     */
    private $rules = array();

    /**
     * @param array $var
     */
    private function __construct(array $var)
    {
        $this->var = $var;
    }

    /**
     * @static
     * @param array $var
     * @return Form_Validation
     */
    public static function factory(array $var)
    {	
        return new Form_Validation($var);
    }

    /**
     * Form kontrol kuralları tanımlanır.
     * @param $key dizi içindeki key adı
     * @param $rule kontrol kuralları
     * @return Form_Validation
     */
    public function rule($key, $rule)
    {
        $this->rules[$key] = $rule;
        return $this;
    }

    /**
     * Verilen anahtarın ( key ) dizi içinde olup olmadığı veya boş/dolu
     * kontrolünü yapar.
     * @param $key dizi içindeki key adı
     * @return bool
     */
    private function required($key)
    {
        $val = trim($this->var[$key]);

        if (empty($val)) {
            return false;
        }

        return true;
    }

    /**
     * Girilen e-mail adresinin geçerliliğini kontrol eder.
     * @param $key dizi içindeki key adı
     * @return bool
     */
    private function valid_email($key)
    {
        $sanitize_email = filter_var($this->var[$key], FILTER_SANITIZE_EMAIL);

        if (filter_var($sanitize_email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    /**
     * Girilen ip adresinin geçerliliğini kontrol eder.
     * @param $key dizi içindeki key adı
     * @return bool
     */
    private function is_ip($key)
    {
        if (filter_var($this->var[$key], FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return true;
    }

    /**
     * Girilen değerin numeric olup olmadığını kontrol eder.
     * @param $key dizi içindeki key adı
     * @return bool
     */
    private function is_int($key)
    {
        if (filter_var($this->var[$key], FILTER_VALIDATE_INT) === false) {
            return false;
        }

        return true;
    }

    /**
     * Girilen değerin string olup olmadığını kontrol eder.
     * @param $key dizi içindeki key adı
     * @return bool
     */
    private function is_string($key)
    {
        return is_string($this->var[$key]);
    }

    /**
     * Değerin minimum uzunluğunu kontrol eder.
     * @param $key dizi içindeki key adı
     * @param $len uzunluk değeri
     * @return bool
     */
    private function min_len($key,$len)
    {
        return (mb_strlen($this->var[$key]) >= $len);
    }

    /**
     * Değerin maximum uzunluğunu kontrol eder.
     * @param $key dizi içindeki key adı
     * @param $len uzunluk değeri
     * @return bool
     */
    private function max_len($key,$len)
    {
        return (mb_strlen($this->var[$key]) <= $len);
    }

    /**
     * Girilen url adresinin geçerliliğini kontrol eder.
     * @param $key dizi içindeki key adı
     * @return bool
     */
    private function valid_url($key)
    {
        $sanitize_url = filter_var($this->var[$key], FILTER_SANITIZE_URL);

        if (filter_var($sanitize_url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return true;
    }

    /**
     * Girilen tarihin geçerliliğini kontrol eder.
     * @param $key dizi içindeki key adı
     * @return bool
     */
    public function valid_date($key)
    {
        return strtotime($this->var[$key]) == true;
    }

    /**
     * Belirtilen kuralları kontrol eden fonksiyon.
     * @throws Exception
     * @return bool
     */
    public function check()
    {
        try
        {
            $is_valid = true;

            if (empty($this->rules)) {
                return true;
            }

            foreach ($this->rules AS $key => $val) {

                $rules = explode('|', $val);

                foreach ($rules AS $rule) {

                    if (!strstr($rule,'max') && !strstr($rule, 'min')) {

                        if (is_callable(array($this, $rule)) === false) {
                            throw new Exception(__CLASS__.' <b>'.$rule.'</b> Call to undefined method');
                        }

                        $is_valid = $this->$rule($key);

                    } else {
                        $rule = explode(':', $rule);

                        if (is_callable(array($this, $rule[0])) === false) {
                            throw new Exception(__CLASS__.' <b>'.$rule[0].'</b> Call to undefined method');
                        }

                        $is_valid = $this->$rule[0]($key, $rule[1]);
                    }
                }
            }

            return $is_valid;

        } Catch (Exception $e) {
            echo '<pre>'.var_export($e,true).'</pre>';
        }
    }
}
