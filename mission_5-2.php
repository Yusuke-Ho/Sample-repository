<html>
<head>
  <meta name="viewport" content="width=320, height=480, initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0, user-scalable=yes"><!-- for smartphone. ここは一旦、いじらなくてOKです。 -->
	<meta charset="utf-8"><!-- 文字コード指定。ここはこのままで。 -->
</head>

<body>

<?php
    // DB接続設定
    $dsn = 'データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    // DB上にテーブル作成
    $sql = "CREATE TABLE IF NOT EXISTS tbtest"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "day DATETIME,"
    . "pass char(32)"
    .");";
    $stmt = $pdo->query($sql);
?>

<?php
    //emptyの代用(0はfalse, スペースのみはtrue)
    if (!function_exists('is_nullorempty')) {
        /**
         * validate string. null is true, "" is true, 0 and "0" is false, " " is false.
         */
        function is_nullorempty($obj)
        {
            if($obj === 0 || $obj === "0"){
                return false;
            }
            return empty($obj);
        }
    }
    
    if (!function_exists('is_nullorwhitespace')) {
        /**
         * validate string. null is true, "" is true, 0 and "0" is false, " " is true.
         */
        function is_nullorwhitespace($obj)
        {
            if(is_nullorempty($obj) === true){
                return true;
            }
            if(is_string($obj) && mb_ereg_match("^(\s|　)+$", $obj)){
                return true;
            }
            return false;
        }
    }
        
    
    // 送信処理
    if (!empty($_POST["submit"])) {
        
        $comment = $_POST["comment"];
        $name = $_POST["name"];
        $date = date("Y-m-d H:i:s");
        $pass1 = $_POST["pass1"];
        $count = 0;
        //フォームが空でないとき(編集したものを送信)   
        if(!empty($_POST["edit_num_h"])){
            $edit_num_h = $_POST["edit_num_h"];
            $id = 3;
            // echo "$edit_num_h";
            $sql = 'UPDATE tbtest SET name=:name,comment=:comment WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':id',$edit_num_h, PDO::PARAM_INT);
            $stmt->execute();
        //フォームが空のとき            
        }else{
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbtest");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            
            
            $result_com = is_nullorwhitespace($comment);
            $result_name = is_nullorwhitespace($name);
            $result_pass = is_nullorwhitespace($pass1);
            
            if($result_com || $result_name || $result_pass){
                
            
            }else{
                $sql = $pdo -> prepare("INSERT INTO tbtest (name, comment, day, pass) VALUES (:name, :comment, :day, :pass)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':day', $date, PDO::PARAM_STR);
                $sql -> bindParam(':pass', $pass1, PDO::PARAM_STR);
                //prepare内を実行
                $sql -> execute();
            }            
        }            
    }   
    
    // 削除処理
    if(!empty($_POST["delete"])) {
        if(is_nullorwhitespace($_POST["del_num"]) || is_nullorwhitespace($_POST["pass2"])){
            
        }else{
            $del_num = $_POST["del_num"];
            $pass2 = $_POST["pass2"];
            $stmt = $pdo->prepare("SELECT * FROM tbtest WHERE id = :id");
            $stmt->bindParam( ':id', $del_num, PDO::PARAM_INT);
            $stmt->execute();
            $passcor = $stmt->fetchAll();

                if($passcor[0]['pass'] == $pass2){
                    $sql = 'delete from tbtest where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $del_num, PDO::PARAM_INT);
                    $stmt->execute();
                }
        }    
    }
    
    $val_1 = NULL;
    $val_2 = NULL;
   
    // 編集処理
    if(empty($_POST["edit"])){
        $val_1 = NULL;
        $val_2 = NULL;
        $edit_num = NULL;
    }else{
        if(empty($_POST["edit_num"])){       
            $val_1 = NULL;
            $val_2 = NULL;
            
        }else{
            $edit_num = $_POST["edit_num"];
            $pass3 = $_POST["pass3"];
            $stmt = $pdo->prepare("SELECT * FROM tbtest WHERE id = :id");
            $stmt->bindParam( ':id', $edit_num, PDO::PARAM_INT);
            $stmt->execute();
            $passcor = $stmt->fetchAll();

                if($passcor[0]['pass'] == $pass3 ){
                    $stmt = $pdo->prepare("SELECT * FROM tbtest WHERE id = :id");
                    $stmt->bindParam( ':id', $edit_num, PDO::PARAM_INT);
                    $stmt->execute();
                    $passcor = $stmt->fetchAll();
                    $val_1 = $passcor[0]['name'];
                    $val_2 = $passcor[0]['comment'];

                }else{
                    $val_1 = NULL;
                    $val_2 = NULL;
                }        

        }    
    }
    
    //DBに存在するidをプルダウンとして表示
    $sql = 'SELECT * FROM tbtest';
    $stmt = $pdo->query($sql);
    $exist_data = $stmt->fetchAll();
      $id_data = NULL;
    foreach($exist_data as $line){
        $id_data .= "<option value='". $line['id']."'>". $line['id']. "</option>";
    }

?>
            
<form method="POST" action=""　>
    <p>新規投稿フォーム
	名前：<input type="text" name="name" value = "<?php echo $val_1; ?>">
	コメント：<input type="text" name="comment" value = "<?php echo $val_2; ?>">
	パスワード：<input type="text" name="pass1" value = "">
	          <input type="submit" name="submit" value="送信"><br></p>
	<p>※編集して送信の際はパスワードは更新されません</p>          
	          
	<p>削除フォーム          
	削除番号：<select name='del_num'><?php echo $id_data; ?></select>
	パスワード：<input type="text" name="pass2" value = "">
	          <input type="submit" name="delete" value="削除"><br></p>
	          
	<p>編集フォーム          
    編集番号：<select name='edit_num'><?php echo $id_data; ?></select>
	パスワード：<input type="text" name="pass3" value = "">
	          <input type="submit" name="edit" value="編集"><br>
	          <input type="hidden" name="edit_num_h" value = "<?php echo $edit_num; ?>"></p>
	          
</form>

<?php
    //DBの中身を表示
    // $sql = 'SELECT * FROM tbtest';
    // $stmt = $pdo->query($sql);
    // $results = $stmt->fetchAll();
    // foreach ($results as $row){
    //     //$rowの中にはテーブルのカラム名が入る
    //     echo $row['id'].',';
    //     echo $row['name'].',';
    //     echo $row['comment'].',';
    //     echo $row['day'].',';
    //     echo $row['pass'].'<br>';
    //     echo "<hr>";
    //         }
?>

</body>
</html>