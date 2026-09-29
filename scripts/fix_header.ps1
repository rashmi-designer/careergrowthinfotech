$p='admin\\candidate-details.php'
$txt = Get-Content -Raw -LiteralPath $p
$txt = $txt -replace '^\s*<\?php\s*\r?\n\s*declare','<?php`ndeclare'
$enc = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($p, $txt, $enc)
Write-Output 'Header fixed'
