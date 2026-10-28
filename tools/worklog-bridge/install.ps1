param(
    [string]$Url,
    [string]$Vault = 'D:\MyFiles\obsidianVault\Vuks Obsidian',
    [string]$StateDirectory = (Join-Path $env:LOCALAPPDATA 'workLOG'),
    [string]$TokenFile,
    [switch]$NoStart
)
$ErrorActionPreference = 'Stop'
if (-not $Url) { $Url = Read-Host 'Website base URL (include /public on production)' }
$uri = [uri]$Url
if ($uri.Scheme -ne 'https' -and $uri.Host -notin @('localhost','127.0.0.1')) { throw 'Use HTTPS outside localhost.' }
if (-not (Test-Path -LiteralPath (Join-Path $Vault 'Work\To Do.md'))) { throw 'Current Obsidian note not found.' }
$nodePath = (Get-Command node -ErrorAction Stop).Source
New-Item -ItemType Directory -Path $StateDirectory -Force | Out-Null
$existingTask = Get-ScheduledTask -TaskName 'workLOG Obsidian Bridge' -ErrorAction SilentlyContinue
if ($existingTask) { Stop-ScheduledTask -TaskName 'workLOG Obsidian Bridge' }
foreach ($name in @('bridge.mjs','markdown.mjs','run.ps1')) {
    Copy-Item -LiteralPath (Join-Path $PSScriptRoot $name) -Destination (Join-Path $StateDirectory $name) -Force
}
if ($TokenFile) {
    $device = Get-Content -Raw -LiteralPath $TokenFile | ConvertFrom-Json
    if ($device.token -notmatch '^[a-f0-9]{64}$') { throw 'Invalid device token file.' }
    $secureToken = ConvertTo-SecureString $device.token -AsPlainText -Force
} else {
    $secureToken = Read-Host 'Paste the device token from workLOG > Obsidian' -AsSecureString
}
$secureToken | ConvertFrom-SecureString | Set-Content -LiteralPath (Join-Path $StateDirectory 'token.dpapi') -Encoding ASCII
$config = @{
    url = $Url.TrimEnd('/')
    vault = [IO.Path]::GetFullPath($Vault)
    currentNote = 'Work/To Do.md'
    inboxNote = 'Work/workLOG/Inbox.md'
    archiveDirectory = 'Work/workLOG/Weeks'
}
[IO.File]::WriteAllText((Join-Path $StateDirectory 'config.json'), ($config | ConvertTo-Json), [Text.UTF8Encoding]::new($false))
$identity = [Security.Principal.WindowsIdentity]::GetCurrent().Name
$grant = $identity + ':(OI)(CI)F'
& icacls.exe $StateDirectory /inheritance:r /grant:r $grant 'SYSTEM:(OI)(CI)F' | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'Could not restrict bridge directory permissions.' }
if (-not (Test-Path -LiteralPath (Join-Path $StateDirectory 'state.json'))) {
    & $nodePath (Join-Path $StateDirectory 'bridge.mjs') --state $StateDirectory --initialize
    if ($LASTEXITCODE -ne 0) { throw 'Initial verification failed. No scheduled task was started.' }
}
$runner = Join-Path $StateDirectory 'run.ps1'
$arguments = '-NoProfile -NonInteractive -ExecutionPolicy Bypass -WindowStyle Hidden -File "' + $runner + '" -NodePath "' + $nodePath + '"'
$action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument $arguments
$trigger = New-ScheduledTaskTrigger -AtLogOn -User $identity
$settings = New-ScheduledTaskSettingsSet -Hidden -StartWhenAvailable -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 1) -ExecutionTimeLimit ([TimeSpan]::Zero) -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries
$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
Register-ScheduledTask -TaskName 'workLOG Obsidian Bridge' -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Force | Out-Null
if (-not $NoStart) { Start-ScheduledTask -TaskName 'workLOG Obsidian Bridge' }
Write-Host 'Bridge installed. Backups, credentials, and logs are private to this Windows account.'
