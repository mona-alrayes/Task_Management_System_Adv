<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $task1=Task::factory()->create([
            'title' => 'task1',
            'description' => 'info about task1',
            'type' => 'Feature',
            'priority' => 'high',
            'due_date' => '20-09-2024',
            'assigned_to' => '4',  // hani
        ]);
        
        $task2=Task::factory()->create([
            'title' => 'task2',
            'description' => 'info about task2',
            'type' => 'Bug',
            'priority' => 'medium',
            'due_date' => '22-09-2024',
            'assigned_to' => '5',  // ayham
        ]);

        $task3=Task::factory()->create([
            'title' => 'task3',
            'description' => 'info about task3',
            'type' => 'Improvement',
            'priority' => 'low',
            'due_date' => '25-09-2024',
            'assigned_to' => '6',  // yosef
        ]);

        $task4= Task::factory()->create([
            'title' => 'task4',
            'description' => 'info about task4',
            'type' => 'Feature',
            'priority' => 'high',
            'due_date' => '20-09-2024',
            'assigned_to' => '5',  // ayham
        ]);
        $task4->dependencies()->sync([1]);    //depends on task1
        $task4->status = 'blocked';
        $task4->save();

        $task5=Task::factory()->create([
            'title' => 'task5',
            'description' => 'info about task5',
            'type' => 'Bug',
            'priority' => 'medium',
            'due_date' => '22-09-2024',
            'assigned_to' => '6',  // yosef
        ]);
        $task5->dependencies()->sync([2]);    //depends on task2
        $task5->status = 'blocked';
        $task5->save();

        $task6=Task::factory()->create([
            'title' => 'task6',
            'description' => 'info about task6',
            'type' => 'Improvement',
            'priority' => 'low',
            'due_date' => '25-09-2024',
            'assigned_to' => '4',  // hani
        ]);
        $task6->dependencies()->sync([3]);    //depends on task3
        $task6->status = 'blocked';
        $task6->save();
    }
}
