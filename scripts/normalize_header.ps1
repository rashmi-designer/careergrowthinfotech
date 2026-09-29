$p='admin\\candidate-details.php'
$txt = Get-Content -Raw -LiteralPath $p
$first = $txt.IndexOf('<?php')
if ($first -gt 0) { $txt = $txt.Substring($first) }
# Replace any whitespace/backtick/newline between <?php and declare with a single CRLF
$txt = $txt -replace '^\<\?php[\s`]*declare','<?php`r`ndeclare'
$enc = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($p, $txt, $enc)
Write-Output 'Header normalized'
