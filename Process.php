<?php 

$filepath = realpath(dirname(__FILE__));
include_once ($filepath.'/../lib/Session.php');
include_once ($filepath.'/../lib/Database.php');
include_once ($filepath.'/../helpers/Format.php');

class Process {
    private $db;
    private $fm;

    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
    }

    public function getProcessData($data) {
        $selectAns = $this->fm->validation($data['ans']);
        $quesnumber = $this->fm->validation($data['quesnumber']);

        $selectAns = mysqli_real_escape_string($this->db->link, $selectAns);
        $quesnumber = mysqli_real_escape_string($this->db->link, $quesnumber);
        $next = $quesnumber + 1;

        // Initialisation du score si nécessaire
        if (!isset($_SESSION['score'])) {
            $_SESSION['score'] = 0;
        }

        // Vérification de la bonne réponse
        $right = $this->rightAns($quesnumber);
        if ($right == $selectAns) {
            $_SESSION['score']++;
        }

        // Enregistrement de la réponse utilisateur
        $userId = Session::get("userId");

        // Vérifie si la réponse existe déjà pour éviter les doublons
        $checkQuery = "SELECT * FROM tbl_userans WHERE userId = '$userId' AND quesNo = '$quesnumber'";
        $checkResult = $this->db->select($checkQuery);
        if ($checkResult) {
            // Met à jour la réponse existante
            $updateQuery = "UPDATE tbl_userans SET ans = '$selectAns' WHERE userId = '$userId' AND quesNo = '$quesnumber'";
            $this->db->update($updateQuery);
        } else {
            // Insère une nouvelle réponse
            $insertQuery = "INSERT INTO tbl_userans(userId, quesNo, ans) VALUES('$userId', '$quesnumber', '$selectAns')";
            $this->db->insert($insertQuery);
        }

        $total = $this->getTotal();
        if ($quesnumber == $total) {
            header("Location:final.php");
            exit();
        } else {
            header("Location:test.php?q=" . $next);
            exit();
        }
    }

    private function getTotal() {
        $query = "SELECT * FROM tbl_ques";
        $result = $this->db->select($query);
        return $result ? $result->num_rows : 0;
    }

    private function rightAns($quesnumber) {
        $query = "SELECT * FROM tbl_ans WHERE quesNo = '$quesnumber' AND rightAns = '1'";
        $result = $this->db->select($query);
        if ($result) {
            $data = $result->fetch_assoc();
            return $data['id'];
        }
        return null;
    }

    public function getUserAnswer($quesNo, $userId) {
        $query = "SELECT ans FROM tbl_userans WHERE quesNo = '$quesNo' AND userId = '$userId'";
        $result = $this->db->select($query);
        if ($result) {
            $data = $result->fetch_assoc();
            return $data['ans'];
        }
        return null;
    }

    public function getUserScore($userId) {
        $query = "
            SELECT COUNT(*) as score
            FROM tbl_userans ua
            INNER JOIN tbl_ans ta ON ua.ans = ta.id
            WHERE ua.userId = '$userId' AND ta.rightAns = '1'
        ";
        $result = $this->db->select($query);
        if ($result) {
            $data = $result->fetch_assoc();
            return $data['score'];
        }
        return 0;
    }
}
?>
