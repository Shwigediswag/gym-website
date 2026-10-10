# Windows shim for the Makefile targets: ./make.ps1 test
param([Parameter(Position=0)][string]$Target = "up")
$php = "docker compose exec php"
$console = "$php php bin/console"
function Run($cmd) { Write-Host ">> $cmd" -ForegroundColor DarkGray; Invoke-Expression $cmd; if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE } }
switch ($Target) {
  "up"        { Run "docker compose up -d" }
  "down"      { Run "docker compose down" }
  "build"     { Run "docker compose build" }
  "sh"        { Run "docker compose exec php bash" }
  "install"   { Run "$php composer install" }
  "db"        { Run "$console doctrine:database:create --if-not-exists" }
  "migrate"   { Run "$console doctrine:migrations:migrate -n" }
  "migration" { Run "$console make:migration" }
  "fixtures"  { Run "$console doctrine:fixtures:load -n" }
  "assets"    { Run "$console tailwind:build --minify"; Run "$console asset-map:compile" }
  "tailwind"  { Run "$console tailwind:build --watch" }
  "lint"      { Run "$console lint:twig templates"; Run "$console lint:yaml config translations"; Run "$console lint:container" }
  "phpstan"   { Run "$php vendor/bin/phpstan analyse --memory-limit=1G" }
  "test"      { & $PSCommandPath lint; & $PSCommandPath phpstan; Run "$console --env=test doctrine:database:create --if-not-exists"; Run "$console --env=test doctrine:migrations:migrate -n"; Run "$php vendor/bin/phpunit" }
  "reset"     { Run "$console doctrine:database:drop --force --if-exists"; & $PSCommandPath db; & $PSCommandPath migrate; & $PSCommandPath fixtures }
  default     { Write-Host "Unknown target '$Target'. See Makefile." ; exit 1 }
}
