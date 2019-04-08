<?php

namespace Slimbug\Transformers;

use Slimbug\Models\Bug;
use League\Fractal\TransformerAbstract;

class BugTransformer extends TransformerAbstract
{

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'author',
    ];

    /**
     * @var integer|null
     */
    protected $requestUserId;

    /**
     * BugTransformer constructor.
     *
     * @param int $requestUserId
     */
    public function __construct($requestUserId = null)
    {
        $this->requestUserId = $requestUserId;
    }

    public function transform(Bug $bug)
    {
        return [
            "slug"            => $bug->slug,
            "title"           => $bug->title,
            "description"     => $bug->description,
            "body"            => $bug->body,
            "tagList"         => optional($bug->tags()->get(['title']))->pluck('title'),
            'createdAt'       => $bug->created_at->toIso8601String(),
            'updatedAt'       => isset($user->update_at) ? $bug->update_at->toIso8601String() : $bug->update_at,
            "favourited"      => $bug->isFavouritedByUser($this->requestUserId),
            "favouritesCount" => $bug->favourites()->count(),
        ];
    }


    /**
     * Include Author
     *
     * @param \Slimbug\Models\Bug $bug
     *
     * @return \League\Fractal\Resource\Item
     * @internal param \Slimbug\Models\Comment $comment
     *
     */
    public function includeAuthor(Bug $bug)
    {
        $author = $bug->user;

        return $this->item($author, new AuthorTransformer($this->requestUserId));
    }

}
