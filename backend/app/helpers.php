<?php

function helper($function_name, $params = NULL)
{
    $function_name = str_replace('.', '', $function_name);
    return require 'HelperFunctions/'.$function_name.'.php';
}

function read_from_response_data($key, $json = FALSE)
{
    $r = require 'HelperFunctions/'.__FUNCTION__.'.php';

    if($json)
    {
        $r = helper('json_str_to_object', $r);        
        $r = clear_object_for_db($r); 
    }
    
    return $r;
}

function clear_object_for_db($obj)
{
    if($obj == NULL) return $obj;
    else if(is_bool($obj))  return (bool)$obj;
    else if(is_numeric($obj)) return $obj;
    else if(is_string($obj))  return helper('clear_string_for_db', $obj);
    else if(is_array($obj))
    {
        foreach($obj as $key => $val)
        {
            if(is_bool($obj[$key])) $obj[$key] = (bool)$obj[$key];
            else if(is_numeric($obj[$key]))  $obj[$key] = $obj[$key];
            else if(is_string($val))   $obj[$key] = helper('clear_string_for_db', $val);
            else  $obj[$key] = clear_object_for_db($val);
        }  
    }
    else if(is_object($obj))
    {
        if(strstr(strtolower(get_class($obj)), 'file')) return $obj;
        
        foreach(array_keys((array)$obj) as $key)
        {
            try 
            {
                if(is_bool($obj->{$key}))  $obj->{$key} = (bool)$obj->{$key};
                if(is_numeric($obj->{$key}))  $obj->{$key} = $obj->{$key};
                else if(is_string($obj->{$key}))  $obj->{$key} = helper('clear_string_for_db', $obj->{$key});
                else $obj->{$key} = clear_object_for_db($obj->{$key});
            } 
            catch (\Exception $e) 
            {
                \Log::alert("helper:".$_SERVER['REQUEST_URI'].':'.json_encode([get_class($obj), array_keys((array)$obj), $obj, $_POST, $e->getMessage()]));
            }
            catch (\Error $e) 
            {
                \Log::alert("helper1:".$_SERVER['REQUEST_URI'].':'.json_encode([get_class($obj), array_keys((array)$obj), $obj, $_POST, $e->getMessage()]));
            }
        }  
    }
    else dd('clear_object_for_db', $obj); 

    return $obj;
}

function get_attr_from_cache($tableName, $requestColumn, $requestData, $responseColumn)
{
    if($requestData == NULL) return NULL;
    if($requestData == 0 && $requestColumn == 'id') return NULL;

    if($requestColumn == $responseColumn) return $requestData;

    if(is_array($requestData)) $requestData = json_encode($requestData);

    $cacheKey = 'tableName:'.$tableName.'|columnName:'.$requestColumn.'|columnData:'.$requestData.'|returnData:'.$responseColumn;
    $value = Cache::rememberForever($cacheKey, function() use($cacheKey, $tableName, $requestColumn, $requestData, $responseColumn)
    {
        $tableName = clear_object_for_db($tableName);
        $requestColumn = clear_object_for_db($requestColumn);
        $requestData = clear_object_for_db($requestData);
        
        $record = DB::table($tableName)->where($requestColumn, $requestData)->first();
        if($record == NULL) return NULL;

        \App\Jobs\CreateRecordCaches::dispatch($record, $tableName);

        if($responseColumn == '*') return $record;

        return @$record->{$responseColumn};
    });

    return $value; 
}

function get_model_from_cache($tableName, $requestColumn, $requestData)
{
    return require 'HelperFunctions/'.__FUNCTION__.'.php';
}

function param_is_have($params, $name)
{
    return require 'HelperFunctions/'.__FUNCTION__.'.php';
}

function param_value_is_correct($params, $name, $validation = '*auto*')
{
    return require 'HelperFunctions/'.__FUNCTION__.'.php';
}

function send_log($level, $message, $object = NULL)
{      
    global $pipe;
    
    if($object == NULL) $object = [];
    if(!is_array($object)) $object = (array)$object;
    
    $user = \Auth::user();
    if($user) $object['user'] = $user->id.'-'.$user->name_basic.' '.$user->surname;
    
    $object['waitTime'] = @helper('get_wait_time');    
    $object['logRandom'] = @$pipe['logRandom'];    
    $object['host'] = @$_SERVER['HOSTNAME'];
    $object['uri'] = @$_SERVER['REQUEST_URI'];
    $object['logTime'] = date("Y-m-d h:i:sa");
    $object['response_data'] = \Request::all();
    $object['ip'] = [@$_SERVER['HTTP_X_REAL_IP'], @$_SERVER['HTTP_X_FORWARDED_FOR'], @$_SERVER['REMOTE_ADDR']];

    \App\Jobs\SendLog::dispatch($level, $message, json_encode($object));
}

function custom_abort($response)
{
    return require 'HelperFunctions/'.__FUNCTION__.'.php';
}

function create_new_record($tableName, $data, $user = NULL)
{
    return require 'HelperFunctions/'.__FUNCTION__.'.php';
}

function copy_record_to_archive($record, $tableName = NULL)
{
    return require 'HelperFunctions/'.__FUNCTION__.'.php';
}

function dd_live($dump, ...$data)
{
    $userId = @\Auth::user()->id;
    if(strlen($userId) == 0) return;
    
    if(count($data) == 1) $data = $data[0];

    $ids = json_decode(DEBUG_USER_IDS);
    if(in_array($userId, $ids)) 
    { 
        if($dump)
        {
            var_dump($data);
            exit(0);
        }
        else dd($data);
    }
} 

function custom_abort_ext($message)
{
    $userId = @\Auth::user()->id;
    if(strlen($userId) == 0) custom_abort($message);
    
    $ids = json_decode(DEBUG_USER_IDS);
    if(!in_array($userId, $ids)) custom_abort($message);
} 

function send_firebese_notify($title, $msg, $user)
{
    if(strlen(@$user->tokens) == 0) return;
    
    $tokens = json_decode($user->tokens);
    if($tokens == NULL || !is_array($tokens)) return;
    
    if(!is_array($tokens))
    {
        \Log::alert('token.is.not.array'.json_encode([$user->tokens, $user]));
        return;
    }
    
    foreach($tokens as $t)
        if(strlen(@$t->clientInfo->firebaseToken) > 0)
            \App\Libraries\MessageLibrary::fireBaseCloudMessaging($title, $msg, $t->clientInfo->firebaseToken);    
}

function tt(...$o)
{
    echo json_encode($o);
    exit(0);
}

function get_base_record()
{
    $n = \Carbon\Carbon::now();
    $user = \Auth::user();
    $userId = $user ? $user->id : ROBOT_USER_ID;

    return 
    [
        'state' => TRUE,
        'created_at' => $n,
        'updated_at' => $n,
        'own_id' => $userId,
        'user_id' => $userId
    ];
}

function run_periodic($start, $end, $period, $stepCallback, $params = NULL)
{
    if(is_string($start)) $start = new \Carbon\Carbon($start);
    if(is_string($end)) $end = new \Carbon\Carbon($end);
    if($end == NULL) $end = new \Carbon\Carbon();

    if($params == NULL) $params = []; 
    if(!is_array($params)) $params = ['params' => $params];
    
    $params['start'] = new \Carbon\Carbon($start->toDateTimeString());
    $params['end'] = $end;
   
    while($start < $end)
    {
        $params['current'] = $start;
        $stepCallback($params != NULL ? $params : NULL);         
        $start->addSeconds($period);
    }

    return $start;
}

function user_language_control()
{
    $lang = get_attr_from_cache('languages', 'id', \Auth::user()->language_id, 'name');
    if(!$lang) return TRUE;
    
    return $lang == DEFAULT_LANGUAGE;
}

function tr($data, ...$args)
{
    $data = trim($data, " \n\r\t");
    if(strlen($data) == 0) return '';
    
    global $pipe;
    if(!isset($pipe['BaseModel'])) $pipe['BaseModel'] = new \App\BaseModel();
    
    $temp = $pipe['BaseModel']->TranslateBasic($data);
    if(count($args) > 0) $temp = sprintf($temp, ...$args);
    
    return $temp;
}