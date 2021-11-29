<?php

namespace App\Db\Service;

use App\Db\Entity\Candidate;
use App\Db\Entity\CandidateTag;
use App\Db\Entity\Expert;
use App\Db\Entity\ExpertTag;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectTag;
use App\Db\Entity\Tag;
use App\Db\Entity\Test;
use App\Db\Entity\TestTag;
use App\Db\Entity\User;
use App\Http\Requests\Tag\GetListRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagsDao
{
    public function searchByNameAndUser(string $tagName, User $user): ?Tag
    {
        /** @var Tag $tag */
        $tag = Tag::query()
            ->whereRaw('LOWER(`name`) LIKE ?', ['%' . $this->reformatTagName($tagName) . '%'])
            ->where('user_id', $user->id)
            ->first();

        return $tag;
    }

    public function search(GetListRequest $request, User $user)
    {
        /** @var Tag[] $tag */
        $tag = Tag::query()
            ->whereRaw('LOWER(`name`) LIKE ?', ['%' . $this->reformatTagName($request->searchString) . '%'])
            ->where('user_id', $user->id)
            ->get();

        return $tag;
    }

    private function reformatTagName(string $tagName)
    {
        $tagName = mb_strtolower($tagName);
        $tagName = trim($tagName);
        $tagName = str_replace(' ', '-', $tagName);
        return $tagName;
    }

    public function createTagByUser(string $tagName, User $user): Tag
    {
        $tag = new Tag();
        $tag->name = $this->reformatTagName($tagName);
        $tag->user_id = $user->id;
        $tag->save();

        return $tag;
    }

    public function createCandidateTag(Candidate $candidate, Tag $tag)
    {
        $candidateTag = new CandidateTag();
        $candidateTag->candidate_id = $candidate->id;
        $candidateTag->tag_id = $tag->id;
        $candidateTag->save();
    }

    public function clearCandidateTags(Candidate $candidate)
    {
        CandidateTag::query()
            ->where('candidate_id', $candidate->id)
            ->delete();
    }

    public function createExpertTag(Expert $expert, Tag $tag)
    {
        $expertTag = new ExpertTag();
        $expertTag->expert_id = $expert->id;
        $expertTag->tag_id = $tag->id;
        $expertTag->save();
    }

    public function clearExpertTags(Expert $expert)
    {
        ExpertTag::query()
            ->where('expert_id', $expert->id)
            ->delete();
    }

    public function createProjectTag(Project $project, Tag $tag)
    {
        $projectTag = new ProjectTag();
        $projectTag->project_id = $project->id;
        $projectTag->tag_id = $tag->id;
        $projectTag->save();
    }

    public function clearProjectTags(Project $project)
    {
        ProjectTag::query()
            ->where('project_id', $project->id)
            ->delete();
    }

    public function createTestTag(Test $test, Tag $tag)
    {
        $testTag = new TestTag();
        $testTag->test_id = $test->id;
        $testTag->tag_id = $tag->id;
        $testTag->save();
    }

    public function clearTestTags(Test $test)
    {
        TestTag::query()
            ->where('test_id', $test->id)
            ->delete();
    }
}
