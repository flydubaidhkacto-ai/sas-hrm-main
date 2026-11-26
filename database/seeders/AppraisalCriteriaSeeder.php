<?php

namespace Database\Seeders;

use App\Models\AppraisalCriterion;
use Illuminate\Database\Seeder;

class AppraisalCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // NOTE: criteria 1-4 were not in the photographed pages: these are PLACEHOLDERS. Edit to match your form.
        $rows = [
            [1, 'one', 'Knowledge of Work', 'Lacks basic knowledge of the job.', 'Has adequate knowledge of the job.', 'Has thorough knowledge of the job.'],
            [2, 'one', 'Quality of Work', 'Work is often inaccurate and needs rework.', 'Work is generally accurate and acceptable.', 'Work is consistently accurate and of high quality.'],
            [3, 'one', 'Productivity', 'Output is below requirement.', 'Output meets requirement.', 'Output consistently exceeds requirement.'],
            [4, 'one', 'Initiative', 'Needs constant direction.', 'Acts on own initiative when required.', 'Highly self-motivated and proactive.'],
            [5, 'one', 'Attendance', 'Tries to come early but late attendance is common', 'Regular but needs improvement', 'Always on time and Punctual'],
            [6, 'one', 'Communication Skills', 'Communication skills impair work performance.', 'Possesses the required communication skills and is effective in the position; meets departmental standards.', 'Has excellent communication skills; very effective in verbal and written interactions.'],
            [7, 'one', 'Work Relationship / Behavior', 'Has trouble getting along with other employees, supervisors and Passengers (Public).', 'Has a generally positive approach in assisting others; maintains effective working relationships; meets departmental standards.', 'Exceeds departmental standards; highly cooperative; works hard to promote positive work relationships.'],
            [8, 'one', 'Safety, Health', 'Fails to follow safety rules, not health concious', 'Follows safety rules and meets departmental standards. Possesses good Health', 'Exceeds departmental standards for safety; maintain good health'],
            [9, 'one', 'Work Supervision / Independability', 'Often fails to perform his/her duty, shirks responsibility, and very poor in supervisory control', 'Conscientious and steady worker; has a good record of dependability. Always possesses the ability to supervise', 'Very conscientious and dependable; performance of his/her duty is excellent; proficient in supervision'],
            [10, 'one', 'Job Ethics', 'Likes to work independently not a team Player', 'Likes to work as member of a team (Good Team Player)', 'Possesses the quality to work independently and as a team member'],
            [11, 'three', 'Overall Work Performance', 'Performance needs improvement to meet Job Requirements.', 'Performance meets Job Requirements', 'Performance exceeds Job Requirements.'],
        ];

        foreach ($rows as [$code, $section, $title, $low, $mid, $high]) {
            AppraisalCriterion::updateOrCreate(['code' => $code], [
                'section' => $section, 'title' => $title,
                'low_text' => $low, 'mid_text' => $mid, 'high_text' => $high,
            ]);
        }
    }
}
