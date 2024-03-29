<?PHP

class Personalsite
{
    
    var $username;
    var $pwd;
    var $database;
    var $tablename;
    var $connection;
    
    var $error_message;
    
    //-----Initialization -------
    
    function InitDB($host,$uname,$pwd,$database,$tablename)
    {
        $this->db_host  = $host;
        $this->username = $uname;
        $this->pwd  = $pwd;
        $this->database  = $database;
        $this->tablename = $tablename;
        
    }
    
    //-------Main Operations ----------------------
    

    function ChangeUserData($id)
    {
        if(!isset($_POST['submitted']))
        {
           return false;
        }
        
        $formvars = array();
        
        
        $this->CollectRegistrationSubmission($formvars);
        
        if(!$this->SaveToDatabase($formvars,$id))
        {
            return false;
        }
                
        return true;
    }

    function UserFullName()
    {
        return isset($_SESSION['name_of_user'])?$_SESSION['name_of_user']:'';
    }

    
    function UserEmail()
    {
        return isset($_SESSION['email'])?$_SESSION['email']:'';
    }


    function GetAgeFromId($id)
    {
        $email = $this->UserEmail();
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($email);
        
        $result = mysql_query("Select age from $this->tablename where userid='$id'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $row = mysql_fetch_assoc($result);

        
        return $row['age'];
    }
    function GetGenderFromId($id)
    {
        $email = $this->UserEmail();
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($email);
        
        $result = mysql_query("Select gender from $this->tablename where userid='$id'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $row = mysql_fetch_assoc($result);

        
        return $row['gender']=='F'?'Female':'Male';
    }
    function GetOcpFromId($id)
    {
        $email = $this->UserEmail();
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($email);
        
        $result = mysql_query("Select occupation from $this->tablename where userid='$id'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $row = mysql_fetch_assoc($result);

        
        return $row['occupation'];
    }
    function GetZCFromId($id)
    {
        $email = $this->UserEmail();
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($email);
        
        $result = mysql_query("Select zip_code from $this->tablename where userid='$id'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $row = mysql_fetch_assoc($result);

        
        return $row['zip_code'];
    }
    function SaveToDatabase(&$formvars,$id)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        if(!$this->Ensuretable())
        {
            return false;
        }
        if(!$this->InsertIntoDB($formvars,$id))
        {
            $this->HandleError("Inserting to Database failed!");
            return false;
        }
        return true;
    }

    function Ensuretable()
    {
        $result = mysql_query("SHOW COLUMNS FROM $this->tablename");   
        if(!$result || mysql_num_rows($result) <= 0)
        {
            return $this->CreateTable();
        }
        return true;
    }

    function InsertIntoDB(&$formvars,$id)
    {
        $insert_query = 'update '.$this->tablename.' SET 
                age=' . $this->SanitizeForSQL($formvars['age']) . ',
                gender = "' . $this->SanitizeForSQL($formvars['gender']) . '",
                occupation = "' . $this->SanitizeForSQL($formvars['occupation']) . '",
                zip_code = "' . $this->SanitizeForSQL($formvars['zipcode']) . '" 
                WHERE userid = ' . $this->SanitizeForSQL($id) . ';
                ';
            
        if(!mysql_query( $insert_query ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
            return false;
        }        
        return true;
    }


    function CollectRegistrationSubmission(&$formvars)
    {
        $formvars['age'] = $this->Sanitize($_POST['age']);
        $formvars['gender'] = $this->Sanitize($_POST['gender']);
        $formvars['occupation'] = $this->Sanitize($_POST['occupation']);
        $formvars['zipcode'] = $this->Sanitize($_POST['zipcode']);
    }
    
   
    //-------Private Helper functions-----------
    
    function HandleError($err)
    {
        $this->error_message .= $err."\r\n";
    }
    
    function HandleDBError($err)
    {
        $this->HandleError($err."\r\n mysqlerror:".mysql_error());
    }
    
    function GetUserFromEmail($email,&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($email);
        
        $result = mysql_query("Select * from $this->tablename where email='$email'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $user_rec = mysql_fetch_assoc($result);

        
        return true;
    }

    function GetDataFromUser($id)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $id = $this->SanitizeForSQL($id);
        
        $result = mysql_query("Select * from $this->tablename where userid='$id'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        while($row = mysql_fetch_assoc($result)){
        	print_r(array_values($row)[0]);
        	echo '<br/>';
        }
        
        return;
    }
    
    function DBLogin()
    {

        $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

        if(!$this->connection)
        {   
            $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
            return false;
        }
        if(!mysql_select_db($this->database, $this->connection))
        {
            $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
            return false;
        }
        if(!mysql_query("SET NAMES 'UTF8'",$this->connection))
        {
            $this->HandleDBError('Error setting utf8 encoding');
            return false;
        }
        return true;
    }    
    
    
    
    
    
    function SanitizeForSQL($str)
    {
        if( function_exists( "mysql_real_escape_string" ) )
        {
              $ret_str = mysql_real_escape_string( $str );
        }
        else
        {
              $ret_str = addslashes( $str );
        }
        return $ret_str;
    }
    
 /*
    Sanitize() function removes any potential threat from the
    data submitted. Prevents email injections or any other hacker attempts.
    if $remove_nl is true, newline chracters are removed from the input.
    */
    function Sanitize($str,$remove_nl=true)
    {
        $str = $this->StripSlashes($str);

        if($remove_nl)
        {
            $injections = array('/(\n+)/i',
                '/(\r+)/i',
                '/(\t+)/i',
                '/(%0A+)/i',
                '/(%0D+)/i',
                '/(%08+)/i',
                '/(%09+)/i'
                );
            $str = preg_replace($injections,'',$str);
        }

        return $str;
    }    
    function StripSlashes($str)
    {
        if(get_magic_quotes_gpc())
        {
            $str = stripslashes($str);
        }
        return $str;
    }    
}
?>