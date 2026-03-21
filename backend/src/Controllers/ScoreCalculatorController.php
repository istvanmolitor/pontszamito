<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ApiResource;
use App\Controller;
use App\ScoreCalculator;

final class ScoreCalculatorController extends Controller
{
    public function calculate(): ApiResource
    {
        $data = $this->getJsonBody();

        $calculator = new ScoreCalculator();

        // Add exam subject results
        if (isset($data['subjects']) && is_array($data['subjects'])) {
            foreach ($data['subjects'] as $subject) {
                if (isset($subject['name'], $subject['level'], $subject['evaluation'])) {
                    $calculator->addExamSubjectResult(
                        (string) $subject['name'],
                        (string) $subject['level'],
                        (int) $subject['evaluation']
                    );
                }
            }
        }

        // Add language exams
        if (isset($data['languageExams']) && is_array($data['languageExams'])) {
            foreach ($data['languageExams'] as $exam) {
                if (isset($exam['language'], $exam['examType'], $exam['evaluation'])) {
                    $calculator->addLanguageExam(
                        (string) $exam['language'],
                        (string) $exam['evaluation'],
                        (string) $exam['examType']
                    );
                }
            }
        }

        return $this->resource([
            'subjects' => $calculator->getExamSubjectResults(),
            'languageExams' => $calculator->getLanguageExams(),
        ]);
    }
}

