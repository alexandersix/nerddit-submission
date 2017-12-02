<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestSeeder extends Seeder
{

    /**
     * The maximum number of users that can be seeded into the users table
     * @var integer
     */
    const MAX_USERS = 1000;

    /**
     * The maximum number of posts that can be associated with a single user
     * @var integer
     */
    const MAX_POSTS_PER_USER = 3;

    /**
     * The maximum number of comments that can be associated with a single post
     * @var integer
     */
    const MAX_COMMENTS_PER_POST = 3;

    /**
     * Flag that determines whether or not the seeder should hit the exact maximum
     * value for Users, Posts, and comments
     *
     * NOTE: Changing this flag to true will make the database seeding take much
     * longer, but it will cause there to be exactly MAX_USERS Users in the users table,
     * and work similarly for Posts and Comments.
     *
     * @var boolean
     */
    const EXACT = false;

    protected $number_of_users;
    protected $number_of_posts;
    protected $number_of_comments;

    protected $desired_number_of_posts = TestSeeder::MAX_POSTS_PER_USER * TestSeeder::MAX_USERS;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->generateUsers();
        $this->generatePosts();
        $this->generateComments();
    }

    /**
     * Uses UserFactory.php script to generate at most MAX_USERS Users with randomly
     * filled attributes.
     *
     */
    private function generateUsers()
    {
        $this->adminUsers();

        $this->number_of_users = 0;
        $entries_completed = false;

        $this->storeUsers(factory(App\User::class, TestSeeder::MAX_USERS)->make());

        if ($this->number_of_users == TestSeeder::MAX_USERS || !TestSeeder::EXACT) {
            $entries_completed = true;
        }


        while (!$entries_completed) {

            $this->storeUsers(factory(App\User::class, TestSeeder::MAX_USERS - $this->number_of_users)->make());

            if ($this->number_of_users == TestSeeder::MAX_USERS) {
                $entries_completed = true;
            }
        }

        echo($this->number_of_users . " out of " . TestSeeder::MAX_USERS . " users created successfully.\n");
    }

    /**
     * Creates specific Users with admin priviledges
     */
    private function adminUsers()
    {
        $now = Carbon::now()->timestamp;

        $created_at = date("Y-m-d H:i:s", $now);
        $updated_at = $created_at;
        $password = Hash::make('Password');

        DB::connection()->getPdo()->exec(
            "INSERT INTO `users` (first_name, last_name, username, email, password, is_admin, remember_token, created_at, updated_at) VALUES ('Alex', 'Six', 'asix', 'asix@clemson.edu', '{$password}', 1, '12345', '{$created_at}', '{$updated_at}')"
        );

        DB::connection()->getPdo()->exec(
            "INSERT INTO `users` (first_name, last_name, username, email, password, is_admin, remember_token, created_at, updated_at) VALUES ('Isaiah', 'Toth', 'itoth', 'itoth@clemson.edu', '{$password}', 1, '12345', '{$created_at}', '{$updated_at}')"
        );

        DB::connection()->getPdo()->exec(
            "INSERT INTO `users` (first_name, last_name, username, email, password, is_admin, remember_token, created_at, updated_at) VALUES ('Pradip', 'Srimani', 'srimani', 'srimani@clemson.edu', '{$password}', 1, '12345', '{$created_at}', '{$updated_at}')"
        );
    }

    /**
     * Stores the array of Users generated by the UserFactory.php script
     * @param  array $users A collection of randomly-generated Users
     */
    private function storeUsers($users)
    {
        foreach ($users as $user) {
            if ($this->storeUser($user)) {
                $this->number_of_users++;
            }
        }
    }

    /**
     * Stores an individual generated User into the users table
     * @param  User $user   A randomly-generated User object
     * @return boolean      True if successful
     *                           False otherwise
     */
    private function storeUser($user)
    {
        try {
            DB::connection()->getPdo()->exec(
                "INSERT INTO `users` (first_name, last_name, username, email, password, is_admin, remember_token, created_at, updated_at) VALUES ('{$user->first_name}', '{$user->last_name}', '{$user->username}', '{$user->email}', '{$user->password}', {$user->is_admin}, '{$user->remember_token}', '{$user->created_at}', '{$user->updated_at}')"
            );

        } catch (\PDOException $e) {
            Log::info($e);
            return false;
        }

        return true;

    }

    /**
     * Uses PostFactory.php script to generate at most (MAX_POSTS_PER_USER * MAX_USERS) Posts with randomly
     * filled attributes.
     *
     */
    private function generatePosts()
    {
        $this->number_of_posts = 0;

        $entries_completed = false;

        $this->storePosts(factory(App\Post::class, $this->desired_number_of_posts)->make());

        if ($this->number_of_posts == $this->desired_number_of_posts || !TestSeeder::EXACT) {
            $entries_completed = true;
        }

        while (!$entries_completed) {

            $this->storePosts(factory(App\Post::class, $this->desired_number_of_posts - $this->number_of_posts)->make());

            if ($this->number_of_posts == $this->desired_number_of_posts) {
                $entries_completed = true;
            }
        }

        echo($this->number_of_posts . " out of " . $this->desired_number_of_posts . " posts created successfully.\n");
    }

    /**
     * Stores the array of Posts generated by the PostFactory.php script
     * @param  array $posts     A collection of randomly-generated Posts
     */
    private function storePosts($posts)
    {
        foreach ($posts as $post) {
            if ($this->storePost($post)) {
                $this->number_of_posts++;
            }
        }
    }

    /**
     * Stores an individual generated Post into the posts table
     * @param  Post $post   A randomly-generated Post object
     * @return boolean      True if successful
     *                           False otherwise
     */
    private function storePost($post)
    {
        try {

            DB::connection()->getPdo()->exec(
                "INSERT INTO `posts` (user_id, title, content, link, likes, created_at, updated_at) VALUES ({$post->user_id}, '{$post->title}', '{$post->content}', '{$post->link}', {$post->likes}, '{$post->created_at}', '{$post->updated_at}')"
            );

        } catch (\PDOException $e) {
            Log::error($e);

            return false;
        }

        return true;
    }

    /**
     * Uses CommentFactory.php script to generate at most ((MAX_POSTS_PER_USER * MAX_USERS) * MAX_COMMENTS_PER_POST) Comments with randomly
     * filled attributes.
     *
     */
    private function generateComments()
    {
        $this->number_of_comments = 0;

        $entries_completed = false;

        $desired_number_of_comments = $this->number_of_posts * TestSeeder::MAX_COMMENTS_PER_POST;

        $this->storeComments(factory(App\Comment::class, $desired_number_of_comments)->make());

        if ($this->number_of_comments == $desired_number_of_comments || !TestSeeder::EXACT) {
            $entries_completed = true;
        }

        while (!$entries_completed) {

            $this->storeComments(factory(App\Comment::class, $this->number_of_comments - $desired_number_of_comments)->make());

            if ($this->number_of_comments == $desired_number_of_comments) {
                $entries_completed = true;
            }
        }

        echo($this->number_of_comments . " out of " . $desired_number_of_comments . " comments created successfully.\n");
    }

    /**
     * Stores the array of Comments generated by the CommentFactory.php script
     * @param  array $comments     A collection of randomly-generated Comments
     */
    private function storeComments($comments)
    {
        foreach ($comments as $comment) {
            if ($this->storeComment($comment)) {
                $this->number_of_comments++;
            }
        }
    }

    /**
     * Stores an individual generated Comment into the comments table
     * @param  Comment $comment   A randomly-generated Comment object
     * @return boolean      True if successful
     *                           False otherwise
     */
    private function storeComment($comment)
    {

        try {

            DB::connection()->getPdo()->exec(
                "INSERT INTO `comments` (user_id, post_id, content, created_at, updated_at) VALUES ({$comment->user_id}, {$comment->post_id}, '{$comment->content}', '{$comment->created_at}', '{$comment->updated_at}')"
            );

        } catch (\PDOException $e) {
            Log::error($e);

            return false;
        }

        return true;
    }
}
