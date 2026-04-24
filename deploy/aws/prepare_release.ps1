param(
    [string]$ProjectRoot = "C:\xampp\htdocs\Poolpal",
    [string]$OutputDir = "$env:USERPROFILE\OneDrive\Desktop",
    [string]$ReleaseName = "poolpal-aws-release.zip"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path -Path $ProjectRoot)) {
    throw "Project root not found: $ProjectRoot"
}

New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null
$zipPath = Join-Path $OutputDir $ReleaseName
if (Test-Path $zipPath) {
    Remove-Item -Path $zipPath -Force
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
$compressionLevel = [System.IO.Compression.CompressionLevel]::Optimal

$root = (Resolve-Path $ProjectRoot).Path
$excludeDirs = @(".git", "_archived", "node_modules", "vendor", "deploy")
$excludeFiles = @("*.zip", "Thumbs.db", ".DS_Store")

$zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    $items = Get-ChildItem -Path $root -Recurse -File | Where-Object {
        $full = $_.FullName
        $relative = $full.Substring($root.Length).TrimStart('\\')
        $parts = $relative.Split('\\')
        $top = if ($parts.Length -gt 0) { $parts[0] } else { "" }
        ($excludeDirs -notcontains $top) -and -not ($excludeFiles | ForEach-Object { $_ } | Where-Object { $relative -like $_ })
    }

    foreach ($file in $items) {
        $relative = $file.FullName.Substring($root.Length).TrimStart('\\')
        $entryName = $relative -replace '\\', '/'
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $file.FullName, $entryName, $compressionLevel) | Out-Null
    }
}
finally {
    $zip.Dispose()
}

Write-Host "Release package created:" -ForegroundColor Green
Write-Host $zipPath
