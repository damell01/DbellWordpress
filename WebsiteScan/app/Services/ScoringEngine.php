<?php
namespace App\Services;

class ScoringEngine {
    // Weight per category (total = 100)
    private array $weights = [
        'seo'           => 30,
        'accessibility' => 25,
        'conversion'    => 25,
        'technical'     => 15,
        'local'         => 5,
    ];

    // Severity penalty points
    private array $severityPenalty = [
        'critical' => 20,
        'high'     => 10,
        'medium'   => 5,
        'low'      => 2,
        'info'     => 0,
    ];

    public function calculate(array $issues): array {
        $categoryIssues = [];
        foreach ($issues as $issue) {
            $cat = $issue['category'] ?? 'technical';
            $categoryIssues[$cat][] = $issue;
        }

        $categoryScores = [];
        foreach (array_keys($this->weights) as $cat) {
            $catIssues = $categoryIssues[$cat] ?? [];
            $categoryScores[$cat] = $this->scoreCategory($cat, $catIssues);
        }

        // Weighted overall score
        $overall = 0;
        foreach ($categoryScores as $cat => $score) {
            $overall += ($score * ($this->weights[$cat] / 100));
        }

        return [
            'overall'       => max(0, min(100, round($overall))),
            'seo'           => $categoryScores['seo'],
            'accessibility' => $categoryScores['accessibility'],
            'conversion'    => $categoryScores['conversion'],
            'technical'     => $categoryScores['technical'],
            'local'         => $categoryScores['local'],
        ];
    }

    private function scoreCategory(string $cat, array $issues): int {
        $penalty = 0;
        foreach ($issues as $issue) {
            $severity = $issue['severity'] ?? 'info';
            $penalty += $this->severityPenalty[$severity] ?? 0;
        }

        // Max possible penalty per category is 100 points
        $score = 100 - min(100, $penalty);
        return max(0, $score);
    }

    public function getGrade(int $score): array {
        return match (true) {
            $score >= 90 => ['grade' => 'A', 'label' => 'Excellent', 'color' => '#22c55e'],
            $score >= 75 => ['grade' => 'B', 'label' => 'Good',      'color' => '#84cc16'],
            $score >= 60 => ['grade' => 'C', 'label' => 'Fair',      'color' => '#f59e0b'],
            $score >= 40 => ['grade' => 'D', 'label' => 'Poor',      'color' => '#ef4444'],
            default      => ['grade' => 'F', 'label' => 'Critical',  'color' => '#dc2626'],
        };
    }

    public function getSummaryText(int $score, array $issues): string {
        $criticalCount = count(array_filter($issues, fn($i) => $i['severity'] === 'critical'));
        $highCount     = count(array_filter($issues, fn($i) => $i['severity'] === 'high'));

        if ($score < 40) {
            return "Your website has serious issues that are costing you visitors and leads. We found {$criticalCount} critical and {$highCount} high-priority problems that need immediate attention.";
        } elseif ($score < 65) {
            return "Your website has significant room for improvement. With the right fixes, you could dramatically increase your visibility and conversion rate.";
        } elseif ($score < 80) {
            return "Your website is performing reasonably well, but there are several areas where improvements would help you get more leads and rank higher in search results.";
        } else {
            return "Your website is in good shape! We found some minor areas for improvement that could give you an extra edge in search rankings and conversions.";
        }
    }

    public function setWeights(array $weights): void {
        foreach ($weights as $key => $value) {
            if (isset($this->weights[$key])) {
                $this->weights[$key] = (int)$value;
            }
        }
    }
}
