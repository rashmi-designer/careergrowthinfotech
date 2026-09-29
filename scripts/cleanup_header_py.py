from pathlib import Path
p = Path('admin/candidate-details.php')
s = p.read_text(encoding='utf-8')
# Replace accidental PowerShell backtick newline marker with real newline
s = s.replace('<?php`ndeclare', '<?php\ndeclare')
# Ensure newline style is CRLF for Windows
s = s.replace('\n', '\r\n')
p.write_text(s, encoding='utf-8', newline='\n')
print('OK')
