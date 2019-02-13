workflow "on pull request merge, delete the branch" {
  on = "pull_request"
  resolves = ["branch cleanup"]
}

action "branch cleanup" {
  uses = "jessfraz/branch-cleanup-action@master"
  secrets = ["GITHUB_TOKEN"]
}

workflow "Package && PR" {
  on = "release"
  resolves = ["Create PR"]
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

action "Create PR" {
  uses = "dz0ny/create-pr@master"
  args = ".github/create_pr.py"
  secrets = ["GITHUB_TOKEN"]
  needs = ["Upload to release"]
}

workflow "Test Project" {
  on = "push"
  resolves = [
    "Lint",
    "Test",
    "Cover"
  ]
}

action "On master or PR" {
  uses = "actions/bin/filter@master"
  args = "branch master|ref refs/pulls/*"
}

action "Install Dependencies" {
  uses = "./.github/php"
  args = "composer install"
  needs = ["On master or PR"]
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

