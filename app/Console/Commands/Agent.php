<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NeuronAI\Chat\Messages\UserMessage;
use App\Neuron\ValidatorAgent;

class Agent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:agent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $response = ValidatorAgent::make()->chat(
        //     new UserMessage("Hi who are you?")
        // );
        // echo $response->getContent();
    }
}
