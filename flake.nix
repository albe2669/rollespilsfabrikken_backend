{
  inputs = {
    flake-utils.url = "github:numtide/flake-utils";
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
  };
  outputs = { self, nixpkgs, flake-utils }:
    flake-utils.lib.eachDefaultSystem (system:
    let
      pkgs = nixpkgs.legacyPackages.${system};
      custom_php = pkgs.php83.buildEnv {
        extensions = {
          enabled,
          all,
        }:
          enabled
          ++ (with all; [
            xdebug
          ]);
        extraConfig = ''
          upload_max_filesize = 1024M
          memory_limit = 2048M
        '';
      };
    in {
      devShells.default = pkgs.mkShell {
        packages = with pkgs; [
          custom_php
          php83Packages.composer
        ];
      };
    });
}

