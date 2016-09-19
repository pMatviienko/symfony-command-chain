# -*- mode: ruby -*-
# vi: set ft=ruby :

#REQUIRED:
#vagrant plugin install vagrant-hostsupdater

#Vagrantfile API/syntax version. Don't touch unless you know what you're doing!

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "ubuntu/trusty64"

    config.vm.provider "virtualbox" do |vb|
        vb.memory = 2048
        vb.cpus = 2
        vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
      end

    config.vm.network :private_network, ip: "192.168.57.31"
    config.vm.hostname = "app"

    config.vm.provision "shell", path: "./vagrant/install-software.sh"
    config.vm.provision "shell", path: "./vagrant/tools/install-php7.sh"
    config.vm.provision "shell", path: "./vagrant/setup-environment.sh"
end
