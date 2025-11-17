<?php
// json.php
// Servește un tabel HTML cu fișiere din folderul `data` (din rădăcina proiectului)
// - Arată numele, mărimea, data modificării, tipul fișierului
// - Dacă fișierul este JSON îl decodează și afișează un preview structurat

function h($s){
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
if(!is_dir($dir)){
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Data folder not found</title>';
    echo '<p>Folderul <code>data</code> nu există în acest proiect. Creează-l și adaugă fișiere acolo.</p>';
    exit;
}

$files = array_values(array_filter(scandir($dir), function($f){ return $f !== '.' && $f !== '..'; }));

// Sortare după dată modificare descrescător
usort($files, function($a,$b) use($dir){
  return filemtime($dir . DIRECTORY_SEPARATOR . $b) - filemtime($dir . DIRECTORY_SEPARATOR . $a);
});

// --- API mode: return JSON for seasons when requested ---
$wantJson = false;
if((isset($_GET['format']) && $_GET['format'] === 'json') || (isset($_GET['api']) && $_GET['api'] === 'seasons')){
  $wantJson = true;
}
// Also accept Accept: application/json
if(!$wantJson && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false){
  $wantJson = true;
}

if($wantJson){
  // datele de sezon (același conținut folosit pentru tabelul din index.php)
  $seasons = [
    'autumn' => [
      [ 'etapa' => 'Scade temperatura sub 30°C (în general)', 'estimare' => '2025-08-15T00:00:00' ],
      [ 'etapa' => 'Primele frunze galbene în copaci', 'estimare' => '2025-09-01T00:00:00' ],
      [ 'etapa' => 'Vânt mai răcoros dimineața', 'estimare' => '2025-09-05T00:00:00' ],
      [ 'etapa' => 'Simți nevoia de geacă dimineața', 'estimare' => '2025-09-10T00:00:00' ],
      [ 'etapa' => 'Început oficial al toamnei', 'estimare' => '2025-09-22T00:00:00' ]
    ],
    'christmas' => [
      [ 'etapa' => 'Primele luminițe aprinse', 'estimare' => '2025-11-25T00:00:00' ],
      [ 'etapa' => 'Începe calendarul de Advent', 'estimare' => '2025-12-01T00:00:00' ],
      [ 'etapa' => 'Împodobirea bradului', 'estimare' => '2025-12-10T00:00:00' ],
      [ 'etapa' => 'Cumpărături de Ajun', 'estimare' => '2025-12-23T00:00:00' ],
      [ 'etapa' => 'Ajunul Crăciunului', 'estimare' => '2025-12-24T00:00:00' ],
      [ 'etapa' => 'Ziua de Crăciun', 'estimare' => '2025-12-25T00:00:00' ]
    ]
  ];

  header('Content-Type: application/json; charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  echo json_encode($seasons, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  exit;
}

header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Lista fișiere — data/</title>
  <style>
    body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial; padding:20px; background:#f7f7f7;}
    .wrap{max-width:1100px;margin:0 auto;background:white;padding:18px;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.06)}
    table{width:100%;border-collapse:collapse;font-size:14px}
    th,td{padding:10px 12px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
    thead th{background:#fafafa;font-weight:700}
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,monospace;font-size:13px;color:#333}
    .small{font-size:12px;color:#666}
    .preview{max-width:620px;white-space:pre-wrap;background:#fbfbfb;border:1px solid #f0f0f0;padding:8px;border-radius:6px}
    .json-table{border:1px solid #eee;margin-top:6px;border-radius:6px}
    .json-table td{border:none;padding:6px 10px}
    .empty{color:#888;padding:14px;text-align:center}
    a.btn{display:inline-block;padding:6px 10px;border-radius:8px;background:#2563eb;color:white;text-decoration:none;margin-bottom:8px}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Conținut folder <code>data/</code></h1>
    <p class="small">Fișiere găsite: <strong><?php echo count($files); ?></strong></p>
    <?php if(count($files) === 0): ?>
      <div class="empty">Folderul <code>data</code> este gol.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Fișier</th>
            <th>Mărime</th>
            <th>Modificat</th>
            <th>Tip</th>
            <th>Previzualizare</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($files as $file):
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $size = is_file($path) ? filesize($path) : 0;
            $mtime = date('Y-m-d H:i:s', filemtime($path));
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $type = is_dir($path) ? 'Folder' : ($ext === '' ? 'Fără extensie' : $ext);
        ?>
          <tr>
            <td class="mono"><a href="data/<?php echo rawurlencode($file); ?>" target="_blank"><?php echo h($file); ?></a></td>
            <td><?php echo $size>0 ? number_format($size) . ' B' : '-'; ?></td>
            <td class="small"><?php echo h($mtime); ?></td>
            <td class="small"><?php echo h($type); ?></td>
            <td>
              <?php
                if(is_dir($path)){
                  echo '<span class="small">(foldere nu sunt expandate)</span>';
                } else {
                  $preview = '';
                  // Citim conținutul fișierului (folosit pentru preview și test JSON)
                  $contents = @file_get_contents($path);
                  // Dacă pare JSON (extensie sau începe cu {/[) încercăm decode
                  if(in_array($ext, ['json','txt']) || ($contents !== false && preg_match('/^\s*[\{\[]/',$contents))){
                    // avem content deja în $contents pentru test
                    $maybe = null;
                    if($contents !== false){
                      $maybe = json_decode($contents, true);
                    }
                    if($contents !== false && json_last_error() === JSON_ERROR_NONE && $maybe !== null){
                      // afișăm tabel JSON
                      echo '<div class="preview">';
                      echo render_json_table($maybe);
                      echo '</div>';
                    } else {
                      // simplu preview text
                      if($contents === false) {
                        echo '<span class="small">Nu se poate citi fișierul.</span>';
                      } else {
                        $short = mb_substr($contents, 0, 800);
                        echo '<div class="preview">' . h($short) . (mb_strlen($contents) > 800 ? "\n... (truncat)" : '') . '</div>';
                      }
                    }
                  } else {
                    // alt tip: imagine/video/binar
                    echo '<span class="small">Tip binar sau nesuportat pentru preview</span>';
                  }
                }
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
<?php
// Funcții auxiliare pentru afișare JSON
function render_json_table($data){
    if(is_array($data)){
        // dacă vector numeric -> listă
        if(array_keys($data) === range(0, count($data)-1)){
            $out = '<table class="json-table"><tbody>';
            foreach($data as $v){
                $out .= '<tr><td>' . render_json_cell($v) . '</td></tr>';
            }
            $out .= '</tbody></table>';
            return $out;
        } else {
            $out = '<table class="json-table"><tbody>';
            foreach($data as $k=>$v){
                $out .= '<tr><td class="mono" style="width:180px;">' . h($k) . '</td><td>' . render_json_cell($v) . '</td></tr>';
            }
            $out .= '</tbody></table>';
            return $out;
        }
    }
    return '<div class="mono">' . h((string)$data) . '</div>';
}

function render_json_cell($v){
    if(is_array($v)) return render_json_table($v);
    if(is_bool($v)) return '<span class="small">' . ($v ? 'true' : 'false') . '</span>';
    if(is_null($v)) return '<span class="small">null</span>';
    return '<span class="mono">' . h((string)$v) . '</span>';
}
