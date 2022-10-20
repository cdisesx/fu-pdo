<?php
namespace fuPdo\mysql;

trait Field
{
    private $selectField = [];

    /**
     * @param $field
     * @return $this
     */
    public function appendSelectField($field)
    {
        if (is_string($field)){
            $this->selectField[] = $field;
        }
        if (is_array($field)){
            $this->selectField = array_merge($this->selectField, $field);
        }
        return $this;
    }

    /**
     * @var bool | array
     */
    protected $saveFields = true;

    /**
     * @param $saveFields
     * @return $this
     */
    public function setSaveFields($saveFields)
    {
        $this->saveFields = $saveFields;
        return $this;
    }

    /**
     * 通过Model中的saveFields,过滤要保存的数据
     * @param $params
     * @return array
     */
    public function filterSaveFields($params)
    {
        if($this->saveFields === true){
            return $params;
        }
        if(is_array($this->saveFields)){
            $saveFieldsMap = array_flip($this->saveFields);
            return array_intersect_key($params, $saveFieldsMap);
        }

        return [];
    }

    /**
     * @var bool | string
     */
    public $createField = false;

    /**
     * @var bool | string
     */
    public $updateField = false;

    /**
     * @var string
     */
    public $timeFormat = "Y-m-d H:i:s";

}