<?php include 'inc/header.php'; ?>

<?php
include_once 'classes/Process.php';
$process = new Process(); // instanciation

Session::checkSession();
$total = $exam->getTotalRows();
$userId = Session::get("userId"); // identifiant utilisateur

// Score basé sur la base de données
$score = $process->getUserScore($userId);
?>

<div class="container">
    <div class="row">
        <div class="col-lg-12 text-center">
            <h1 class="mt-5">Toutes les questions et réponses - Total : <?php echo $total; ?> questions</h1>

            <p class="text-center" style="font-size: 20px;">
                <h1>Vos réponses corrigés </h1>
            </p>

            <br/><br/>
        </div>

        <div class="col-lg-3"></div>

        <div class="col-lg-6">
            <table>
                <?php
                $getQues = $exam->getqueData();

                if ($getQues) {
                    while ($question = $getQues->fetch_assoc()) {
                        $quesNo = $question['quesNo'];
                        $userAnsId = $process->getUserAnswer($quesNo, $userId);
                        ?>
                        <tr>
                            <td colspan="2">
                                <h5>Q<?php echo $quesNo; ?>: <?php echo $question['ques']; ?></h5>
                            </td>
                        </tr>
                        <?php
                        $answer = $exam->getAnswer($quesNo);
                        if ($answer) {
                            while ($result = $answer->fetch_assoc()) {
                                $isCorrect = $result['rightAns'] == '1';
                                $isUserAns = $result['id'] == $userAnsId;

                                echo "<tr><td><input type='radio' disabled ";
                                if ($isUserAns) echo "checked ";
                                echo "/>";

                                if ($isCorrect) {
                                    echo "<span style='color:green;font-weight:bold;'>".$result['ans']." (Bonne réponse)</span>";
                                } elseif ($isUserAns) {
                                    echo "<span style='color:red;font-weight:bold;'>".$result['ans']." (Votre réponse incorrecte)</span>";
                                } else {
                                    echo $result['ans'];
                                }

                                echo "</td></tr>";
                            }
                        }
                    }
                }
                ?>
            </table>

            <br/>
            <a href="starttest.php" class="btn btn-success btn-lg">
                <span class="fa fa-arrow-right"></span> Refaire l'examen
            </a>
            <br/><br/>
        </div>

        <div class="col-lg-3"></div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
