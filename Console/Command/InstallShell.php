<?php

class InstallShell extends AppShell{
	
	public function main(){
		$this->hr();
		$this->out(__d('like', 'Creation of Likes table'));
		$this->hr();
		
		if (!config('database')) {
			$this->out(__d('cake_console', 'Your database configuration was not found. Take a moment to create one.'), true);
			return $this->DbConfig->execute();
		}else{
			$this->dispatchShell('schema create --plugin Like');
			return true;
		}
	}
	
}