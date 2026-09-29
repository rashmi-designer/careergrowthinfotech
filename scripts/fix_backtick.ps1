$p='admin\\candidate-details.php'
$txt = Get-Content -Raw -LiteralPath $p
$txt = $txt -replace '<?php`ndeclare','<?php`r`ndeclare'
$enc = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($p, $txt, $enc)
Write-Output 'Fixed backtick newline'
