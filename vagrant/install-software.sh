#!/bin/bash
#
cd /vagrant

sudo apt-get install -y language-pack-en-base
sudo update-locale LANG=en_US.UTF-8 LC_MESSAGES=POSIX
sudo locale-gen

sudo apt-get update
sudo apt-get -y upgrade

echo "[Info] Installing mc"
sudo apt-get -y install mc

echo "[Info] Installing git"
sudo apt-get -y install git

