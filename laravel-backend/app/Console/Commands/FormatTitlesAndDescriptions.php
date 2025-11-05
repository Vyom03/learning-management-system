<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helpers\TitleFormatter;

class FormatTitlesAndDescriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'format:titles-descriptions {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Format all existing quiz and video titles and descriptions to Title Case';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Starting to format titles and descriptions...');
        $this->newLine();

        // Format quizzes
        $this->info('📝 Processing Quizzes...');
        $quizzes = DB::table('quizzes')->get();
        $quizCount = 0;
        $quizUpdated = 0;

        foreach ($quizzes as $quiz) {
            $quizCount++;
            $originalTitle = $quiz->title;
            $originalDescription = $quiz->description;
            
            $formattedTitle = TitleFormatter::formatTitle($originalTitle);
            $formattedDescription = $originalDescription 
                ? TitleFormatter::formatDescription($originalDescription) 
                : null;

            $titleChanged = $originalTitle !== $formattedTitle;
            $descriptionChanged = $originalDescription !== $formattedDescription;

            if ($titleChanged || $descriptionChanged) {
                $this->line("  Quiz #{$quiz->id}: {$originalTitle}");
                
                if ($titleChanged) {
                    $this->line("    Title: '{$originalTitle}' → '{$formattedTitle}'");
                }
                
                if ($descriptionChanged && $originalDescription) {
                    $descPreview = mb_strlen($originalDescription) > 50 
                        ? mb_substr($originalDescription, 0, 50) . '...' 
                        : $originalDescription;
                    $formattedPreview = mb_strlen($formattedDescription) > 50 
                        ? mb_substr($formattedDescription, 0, 50) . '...' 
                        : $formattedDescription;
                    $this->line("    Description: '{$descPreview}' → '{$formattedPreview}'");
                }

                if (!$dryRun) {
                    DB::table('quizzes')
                        ->where('id', $quiz->id)
                        ->update([
                            'title' => $formattedTitle,
                            'description' => $formattedDescription,
                            'updated_at' => now()
                        ]);
                    $quizUpdated++;
                } else {
                    $quizUpdated++;
                }
            }
        }

        $this->info("  Processed {$quizCount} quizzes, {$quizUpdated} needed updates");
        $this->newLine();

        // Format videos
        $this->info('🎥 Processing Videos...');
        $videos = DB::table('video_content')->get();
        $videoCount = 0;
        $videoUpdated = 0;

        foreach ($videos as $video) {
            $videoCount++;
            $originalTitle = $video->title;
            $originalDescription = $video->description;
            
            $formattedTitle = TitleFormatter::formatTitle($originalTitle);
            $formattedDescription = $originalDescription 
                ? TitleFormatter::formatDescription($originalDescription) 
                : null;

            $titleChanged = $originalTitle !== $formattedTitle;
            $descriptionChanged = $originalDescription !== $formattedDescription;

            if ($titleChanged || $descriptionChanged) {
                $this->line("  Video #{$video->id}: {$originalTitle}");
                
                if ($titleChanged) {
                    $this->line("    Title: '{$originalTitle}' → '{$formattedTitle}'");
                }
                
                if ($descriptionChanged && $originalDescription) {
                    $descPreview = mb_strlen($originalDescription) > 50 
                        ? mb_substr($originalDescription, 0, 50) . '...' 
                        : $originalDescription;
                    $formattedPreview = mb_strlen($formattedDescription) > 50 
                        ? mb_substr($formattedDescription, 0, 50) . '...' 
                        : $formattedDescription;
                    $this->line("    Description: '{$descPreview}' → '{$formattedPreview}'");
                }

                if (!$dryRun) {
                    DB::table('video_content')
                        ->where('id', $video->id)
                        ->update([
                            'title' => $formattedTitle,
                            'description' => $formattedDescription,
                            'updated_at' => now()
                        ]);
                    $videoUpdated++;
                } else {
                    $videoUpdated++;
                }
            }
        }

        $this->info("  Processed {$videoCount} videos, {$videoUpdated} needed updates");
        $this->newLine();

        // Summary
        $totalUpdated = $quizUpdated + $videoUpdated;
        
        if ($dryRun) {
            $this->info("✅ DRY RUN COMPLETE");
            $this->info("   Found {$totalUpdated} records that would be updated");
            $this->warn("   Run without --dry-run to apply changes");
        } else {
            $this->info("✅ Formatting complete!");
            $this->info("   Updated {$quizUpdated} quizzes");
            $this->info("   Updated {$videoUpdated} videos");
            $this->info("   Total: {$totalUpdated} records updated");
        }

        return Command::SUCCESS;
    }
}

