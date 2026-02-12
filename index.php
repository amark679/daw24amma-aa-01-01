<?php
require 'vendor/autoload.php';

// Variable para guardar el resultado y mostrarlo después
$emaitza = "";
$errorea = "";
// Solo ejecutamos esto si se ha enviado el formulario
if (isset($_POST['itzuli'])) {
    
    // 1. Recogemos los datos del formulario
    $testua = $_POST['testua'] ?? '';
    $hizkuntza = $_POST['hizkuntza'] ?? '';

    // Validamos que no estén vacíos
    if (!empty($testua) && !empty($hizkuntza)) {
        
        try {
            // 2. Cargamos el entorno
            
            $apiKey = getenv('OPENAI_API_KEY');
			
			if (!$apiKey) {
                throw new Exception("Ez da aurkitu OPENAI_API_KEY. Ziurtatu Windows-eko ingurune-aldagaietan dagoela.");
            }

            // 3. Creamos el cliente
            $client = OpenAI::client($apiKey);

            // 4. Preparamos el Prompt 
            // Traducimos el valor del radio button a una instrucción clara
            $targetLang = match ($hizkuntza) {
                'basque' => 'Euskera (Basque)',
                'spanish' => 'Spanish',
                'english' => 'English',
                default => 'Spanish'
            };

            // 5. Llamada a la API (Chat Completion)
            $response = $client->chat()->create([
                'model' => 'gpt-4o', // O 'gpt-3.5-turbo' si quieres ahorrar
                'messages' => [
                    ['role' => 'system', 'content' => 'Itzultzaile profesionala zara. Zure erantzunak eta oharrak beti Euskaraz izan behar dira. Ahalik eta esplikazio gutxien eman behar duzu, erantzuna soilik'],
                    ['role' => 'user', 'content' => "Itzuli ondorengo testua hizkuntza honetara ($targetLang): \"$testua\""],
                ],
            ]);

            // 6. Guardamos el resultado
            $emaitza = $response->choices[0]->message->content;

        } catch (Exception $e) {
            $emaitza = "Errorea: " . $e->getMessage();
        }
    } else {
        $emaitza = "Mesedez, idatzi testua eta aukeratu hizkuntza bat.";
    }
}
?>

<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testuen Itzultzailea</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }

        h1 {
            color: #000;
            margin-bottom: 20px;
        }

        form {
            background-color: #e0e0e0;
            padding: 5px;
            border: 1px solid #888;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            border: 1px solid #888;
            padding: 10px;
            vertical-align: middle;
        }

        .emaitza {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            width: 400px;
            min-height: 50px;
        }
    </style>
</head>
<body>

    <h1>Testuen itzultzailea</h1>

    <form action="index.php" method="POST">
        <table>
            <tr>
                <td class="col-left">Testua</td>
                <td class="col-right">
                    <textarea name="testua" placeholder="Idatzi hemen..."><?php echo isset($_POST['testua']) ? htmlspecialchars($_POST['testua']) : ''; ?></textarea>
                </td>
            </tr>

            <tr>
                <td class="col-left">
                    <input type="radio" name="hizkuntza" value="basque" id="eu" <?php if(isset($_POST['hizkuntza']) && $_POST['hizkuntza'] == 'basque') echo 'checked'; ?>>
                </td>
                <td class="col-right">
                    <label for="eu">Euskera</label>
                </td>
            </tr>

            <tr>
                <td class="col-left">
                    <input type="radio" name="hizkuntza" value="spanish" id="es" <?php if(isset($_POST['hizkuntza']) && $_POST['hizkuntza'] == 'spanish') echo 'checked'; ?>>
                </td>
                <td class="col-right">
                    <label for="es">Castellano</label>
                </td>
            </tr>

            <tr>
                <td class="col-left">
                    <input type="radio" name="hizkuntza" value="english" id="en" <?php if(isset($_POST['hizkuntza']) && $_POST['hizkuntza'] == 'english') echo 'checked'; ?>>
                </td>
                <td class="col-right">
                    <label for="en">Inglés</label>
                </td>
            </tr>

            <tr>
                <td class="col-left"></td> 
                <td class="col-right">
                    <button type="submit" name="itzuli">Itzuli</button>
                </td>
            </tr>
        </table>
    </form>

    <?php if (!empty($emaitza)): ?>
        <div class="emaitza">
            <strong>Emaitza:</strong><br><br>
            <?php echo nl2br(htmlspecialchars($emaitza)); ?>
        </div>
    <?php endif; ?>

</body>
</html>