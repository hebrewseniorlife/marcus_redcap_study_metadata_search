<?php

use League\Csv\Writer;

class DocumentHelper {        
    /**
     * writeToCsv
     *
     * @param  mixed $documents
     * @return string
     */
    static function writeToCsv(array $documents) : string {
        if (count($documents) == 0)
        {
            return "";
        }
        
        $headers = array_keys($documents[0]);
        $records = [];

        foreach($documents as $document){
            array_push($records, array_values($document));
        }
        
        $csv = Writer::createFromString();
        $csv->insertOne($headers);
        $csv->insertAll($records);
          
        return $csv->getContent();         
    }

    /**
     * writeToCsv
     *
     * @param  mixed $documents
     * @return string
     */
    static function writeMetadataToCsv(array $documents) : string 
    {
        if (count($documents) == 0)
        {
            return "";
        }
        
        $headers = DocumentHelper::getMetadataLabels();
        $records = [];

        foreach($documents as $document){
            array_push($records, DocumentHelper::getMetadataValues($document));
        }
      
        $csv = Writer::createFromString();
        $csv->insertOne($headers);
        $csv->insertAll($records);
          
        return $csv->getContent();   
    }
        
    /**
     * writeMetadataToZip
     *
     * @param  mixed $documents
     * @return string
     */
    static function writeMetadataToZip(array $documents) : string
    {
        $tempFilename = constant("APP_PATH_TEMP").'study_metadata_cart_'.time().'zip';

        $zip = new ZipArchive();

        if ($zip->open($tempFilename, ZipArchive::CREATE)!==TRUE) {
            throw new Exception("Cannot create zip file in REDCap temp folder. Path was ".$tempFilename);
        }
        
        $zip->addFromString('instrument.csv', DocumentHelper::writeMetadataToCsv($documents));
        $zip->addFromString('OriginID.txt', constant('SERVER_NAME'));
        $zip->close();

        return $tempFilename;
    }

    /**
     * flatten
     *
     * @param  mixed $entity
     * @return void
     */
    public static function flatten($entity = [])
    {
        $flat = [];

        foreach($entity as $key => $value){
            if (is_array($value)){
                $flat = array_merge($flat, DocumentHelper::flatten($value));
            }
            else{
                $flat[$key] = $value;
            }
        }
        return $flat;
    }
    
    /**
     * flattenAll
     *
     * @param  mixed $documents
     * @return void
     */
    public static function flattenAll(array &$documents)
    {
        foreach($documents as $key => $value)
        {
            $documents[$key] = DocumentHelper::flatten($documents[$key]);
        }
    }
        
    /**
     * setOrder
     *
     * @param  array &$documents
     * @param  string $orderFieldName
     * @return void
     */
    public static function setFieldOrder(array &$documents, string $orderFieldName = 'field_order')
    {
        $order = 1;
        foreach($documents as $key => $value)
        {
            $documents[$key]->{$orderFieldName} = $order++; 
        }
    }

    /**
     * getMetadata
     *
     * @param  mixed $context
     * @return array
     */
    public static function getMetadataValues($document = []) : array{
        $keys = DocumentHelper::getMetadataKeys();
        $data = DocumentHelper::flatten($document);

        $values = [];
        foreach($keys as $key){
            array_push($values, $data[$key]);
        }
        return $values;
    }
    
    /**
     * getMetadataKeys
     *
     * @return array
     */
    public static function getMetadataKeys() : array {
        return array_keys(DocumentHelper::$METADATA_PROPERIES);
    }
    
    /**
     * getMetadataLabels
     *
     * @return array
     */
    public static function getMetadataLabels() : array {
        return array_values(DocumentHelper::$METADATA_PROPERIES);
    }

    public static $METADATA_PROPERIES = [
        'field_name' => 'Variable / Field Name',
        'form_name' => 'Form Name',
        'section_header' => 'Section Header',
        'field_type' => 'Field Type',
        'field_label' => 'Field Label',
        'select_choices_or_calculations' => 'Choices, Calculations, OR Slider Labels',
        'field_note' => 'Field Note',
        'text_validation_type_or_show_slider_number' => 'Text Validation Type OR Show Slider Number',
        'text_validation_min' => 'Text Validation Min',
        'text_validation_max' => 'Text Validation Max',
        'identifier' => 'Identifier?',
        'branching_logic' => 'Branching Logic (Show field only if...)',
        'required_field' => 'Required Field?',
        'custom_alignment' => 'Custom Alignment',
        'question_number' => 'Question Number (surveys only)',
        'matrix_group_name' => 'Matrix Group Name',
        'matrix_ranking' => 'Matrix Ranking?',
        'field_annotation' => 'Field Annotation'        
    ];
}