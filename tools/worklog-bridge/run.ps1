param([Parameter(Mandatory=$true)][string]$NodePath)
$ErrorActionPreference = 'Stop'
$log = Join-Path $PSScriptRoot 'bridge.log'
if ((Test-Path -LiteralPath $log) -and (Get-Item -LiteralPath $log).Length -gt 5MB) {
    Move-Item -LiteralPath $log -Destination (Join-Path $PSScriptRoot 'bridge.previous.log') -Force
}
& $NodePath (Join-Path $PSScriptRoot 'bridge.mjs') --state $PSScriptRoot *>> $log
exit $LASTEXITCODE
