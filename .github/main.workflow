workflow "Package" {
  on = "release"
  resolves = ["Upload to release"]
}

action "Build package" {
  uses = "./.github/php"
  args = "make build"
}

action "Upload to release" {
  uses = "JasonEtco/upload-to-release@master"
  args = "build/woocart-defaults.zip application/zip"
  secrets = ["GITHUB_TOKEN"]
  needs = ["Build package"]
}


workflow "Test Project" {
  on = "push"
  resolves = ["Lint", "Test", "Cover"]
}

action "Install Dependencies" {
  uses = "./.github/php"
  args = "composer install"
}

action "Lint" {
  uses = "./.github/php"
  args = "make fmt"
  needs = ["Install Dependencies"]
}

action "Test" {
  uses = "./.github/php"
  args = "make test"
  needs = ["Install Dependencies"]
}

action "Cover" {
  uses = "./.github/php"
  args = "make test cover"
  needs = ["Test"]
}
