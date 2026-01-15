<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ManageUserTokens extends Command
{
    protected $signature = 'tokens:manage
                            {user_id? : User ID for single user operation}
                            {--role_id=1 : Role ID for group operations}
                            {--list : List all tokens}
                            {--create : Create new token}
                            {--name=api-token : Token name}
                            {--abilities=* : Token abilities}
                            {--delete : Delete tokens}
                            {--delete-all : Delete ALL tokens for user(s)}';

    protected $description = 'Manage API tokens for users';

    public function handle()
    {
        $userId = $this->argument('user_id');

        // Если указан user_id - работаем с одним пользователем
        if ($userId) {
            return $this->handleSingleUser($userId);
        }

        // Иначе работаем с группой пользователей по role_id
        return $this->handleUserGroup();
    }

    protected function handleSingleUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return Command::FAILURE;
        }

        $this->info("Managing tokens for user: {$user->name} ({$user->email})");
        $this->line("Position ID: " . ($user->position_id ?? 'NULL'));
        $this->line("Role: " . ($user->role->name ?? 'N/A'));

        if ($this->option('list')) {
            return $this->listUserTokens($user);
        }

        if ($this->option('create')) {
            return $this->createUserToken($user);
        }

        if ($this->option('delete')) {
            return $this->deleteUserTokens($user);
        }

        if ($this->option('delete-all')) {
            return $this->deleteAllUserTokens($user);
        }

        // По умолчанию показываем список токенов пользователя
        return $this->listUserTokens($user);
    }

    protected function handleUserGroup()
    {
        $roleId = $this->option('role_id');
        $users = User::where('role_id', $roleId)->get();

        if ($users->isEmpty()) {
            $this->error("No users found with role_id = {$roleId}");
            return Command::FAILURE;
        }

        if ($this->option('list')) {
            return $this->listTokens($users);
        }

        if ($this->option('create')) {
            return $this->createTokens($users);
        }

        if ($this->option('delete')) {
            return $this->deleteTokens($users);
        }

        if ($this->option('delete-all')) {
            return $this->deleteAllTokens($users);
        }

        // По умолчанию показываем список
        return $this->listTokens($users);
    }

    protected function listUserTokens(User $user)
    {
        $this->info("Tokens for user: {$user->name}");

        $tokensData = [];

        foreach ($user->tokens as $token) {
            $tokensData[] = [
                'token_id' => $token->id,
                'token_name' => $token->name,
                'abilities' => implode(', ', $token->abilities),
                'created' => $token->created_at->format('Y-m-d H:i:s'),
                'last_used' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never',
            ];
        }

        if (empty($tokensData)) {
            $this->warn("No tokens found for this user.");
            return Command::SUCCESS;
        }

        $this->table(
            ['Token ID', 'Token Name', 'Abilities', 'Created', 'Last Used'],
            $tokensData
        );

        return Command::SUCCESS;
    }

    protected function createUserToken(User $user)
    {
        $tokenName = $this->option('name');
        $abilities = $this->option('abilities') ?: ['*'];

        // Проверяем, нет ли уже токена с таким именем
        $existingToken = $user->tokens()->where('name', $tokenName)->first();

        if ($existingToken) {
            if (!$this->confirm("User {$user->name} already has token '{$tokenName}'. Replace it?")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
            $existingToken->delete();
        }

        $token = $user->createToken($tokenName, $abilities);
        $plainTextToken = $token->plainTextToken;

        $this->info("✅ Token created successfully!");
        $this->newLine();

        $this->line("User: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Position Level: " . ($user->position->level ?? 'N/A'));
        $this->line("Token Name: {$tokenName}");
        $this->line("Abilities: " . implode(', ', $abilities));
        $this->newLine();

        $this->warn("⚠️  FULL TOKEN (copy carefully):");
        $this->newLine();
        $this->line($plainTextToken);
        $this->newLine();

        $this->info("Usage example:");
        $this->line("curl -X GET \\");

        $this->line("  'http://localhost:8000/api/available-cars' \\");
        $this->line("  -H 'Authorization: Bearer {$plainTextToken}' \\");
        $this->line("  -H 'Accept: application/json'");
        $this->newLine();

        $this->warn('⚠️  Save this token now! It will not be shown again.');

        // Сохраняем в файл
        $this->saveTokenToFile($user, $plainTextToken, $tokenName);

        return Command::SUCCESS;
    }

    protected function deleteUserTokens(User $user)
    {
        $tokenName = $this->option('name');
        $tokens = $user->tokens();

        if ($tokenName !== 'api-token') {
            $tokens = $tokens->where('name', $tokenName);
        }

        $count = $tokens->count();

        if ($count === 0) {
            $this->warn("No tokens found for user {$user->name} with name '{$tokenName}'");
            return Command::SUCCESS;
        }

        if (!$this->confirm("Delete {$count} token(s) for user {$user->name}?")) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $tokens->delete();
        $this->info("✅ Deleted {$count} token(s) for user {$user->name}");

        return Command::SUCCESS;
    }

    protected function deleteAllUserTokens(User $user)
    {
        $count = $user->tokens()->count();

        if ($count === 0) {
            $this->warn("No tokens found for user {$user->name}");
            return Command::SUCCESS;
        }

        if (!$this->confirm("Delete ALL {$count} tokens for user {$user->name}?")) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $user->tokens()->delete();
        $this->info("✅ Deleted ALL {$count} tokens for user {$user->name}");

        return Command::SUCCESS;
    }

    protected function listTokens($users)
    {
        $this->info("Tokens for users with role_id = {$this->option('role_id')}:");

        $tokensData = [];

        foreach ($users as $user) {
            foreach ($user->tokens as $token) {
                $tokensData[] = [
                    'user_id' => $user->id,
                    'user' => $user->name,
                    'email' => $user->email,
                    'position_level' => $user->position->level ?? 'N/A',
                    'token_name' => $token->name,
                    'token_id' => $token->id,
                    'abilities' => implode(', ', $token->abilities),
                    'created' => $token->created_at->format('Y-m-d H:i:s'),
                    'last_used' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never',
                ];
            }
        }

        if (empty($tokensData)) {
            $this->warn("No tokens found for these users.");
            return Command::SUCCESS;
        }

        $this->table(
            ['User ID', 'User', 'Email', 'Position', 'Token Name', 'Token ID', 'Abilities', 'Created', 'Last Used'],
            $tokensData
        );

        return Command::SUCCESS;
    }

    protected function createTokens($users)
    {
        $tokenName = $this->option('name');
        $abilities = $this->option('abilities') ?: ['*'];

        $this->info("Creating tokens for {$users->count()} users...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $createdTokens = [];
        $fullTokens = [];

        foreach ($users as $user) {
            // Проверяем, нет ли уже токена с таким именем
            $existingToken = $user->tokens()->where('name', $tokenName)->first();

            if ($existingToken) {
                $this->warn("User {$user->name} already has token '{$tokenName}'");
                $bar->advance();
                continue;
            }

            $token = $user->createToken($tokenName, $abilities);
            $plainTextToken = $token->plainTextToken;

            $createdTokens[] = [
                'user' => $user->name,
                'email' => $user->email,
                'token' => $plainTextToken,
                'name' => $tokenName,
            ];

            $fullTokens[] = [
                'user' => $user->name,
                'email' => $user->email,
                'position_level' => $user->position->level ?? 'N/A',
                'full_token' => $plainTextToken,
                'name' => $tokenName,
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (!empty($createdTokens)) {
            $this->info("Created tokens:");
            $this->table(
                ['User', 'Email', 'Token Name', 'Token'],
                array_map(function ($item) {
                    return [
                        $item['user'],
                        $item['email'],
                        $item['name'],
                        $item['token'],
                    ];
                }, $createdTokens)
            );

            $this->newLine();
            $this->warn('⚠️  IMPORTANT: Full tokens saved to file!');

            // Сохраняем полные токены в файл
            $this->saveTokensToFile($fullTokens);
        }

        return Command::SUCCESS;
    }

    protected function deleteTokens($users)
    {
        if ($this->confirm('Are you really want to delete users tokens?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $tokenName = $this->option('name');
        $totalCount = 0;

        foreach ($users as $user) {
            $count = $user->tokens()->where('name', $tokenName)->delete();
            $totalCount += $count;
        }

        $this->info("Deleted {$totalCount} token(s) with name '{$tokenName}' from {$users->count()} users.");

        return Command::SUCCESS;
    }

    protected function deleteAllTokens($users)
    {
        if (!$this->confirm('Are you sure you want to delete ALL tokens for ALL these users?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $totalDeleted = 0;

        foreach ($users as $user) {
            $count = $user->tokens()->delete();
            $totalDeleted += $count;
        }

        $this->info("Deleted {$totalDeleted} tokens from {$users->count()} users.");

        return Command::SUCCESS;
    }

    protected function saveTokenToFile(User $user, $token, $tokenName)
    {
        $filename = 'token_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.txt';
        $filepath = storage_path('app/' . $filename);

        $content = "Token for user: {$user->name}\n";
        $content .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $content .= "==========================================\n\n";
        $content .= "User ID: {$user->id}\n";
        $content .= "Name: {$user->name}\n";
        $content .= "Email: {$user->email}\n";
        $content .= "Position Level: " . ($user->position->level ?? 'N/A') . "\n";
        $content .= "Token Name: {$tokenName}\n";
        $content .= "Full Token: {$token}\n\n";
        $content .= "Usage:\n";
        $content .= "Authorization: Bearer {$token}\n\n";
        $content .= "Curl Example:\n";
        $content .= "curl -X GET \\\n";
        $content .= "  'http://localhoost:8000/api/available-cars' \\\n";
        $content .= "  -H 'Authorization: Bearer {$token}' \\\n";
        $content .= "  -H 'Accept: application/json'\n";

        file_put_contents($filepath, $content);
        $this->info("✅ Token saved to: " . $filepath);
    }

    protected function saveTokensToFile($tokens)
    {
        $filename = 'tokens_group_' . date('Y-m-d_H-i-s') . '.txt';
        $filepath = storage_path('app/' . $filename);

        $content = "Tokens generated on " . date('Y-m-d H:i:s') . "\n";
        $content .= "==========================================\n\n";

        foreach ($tokens as $token) {
            $content .= "User: " . $token['user'] . "\n";
            $content .= "Email: " . $token['email'] . "\n";
            $content .= "Position Level: " . $token['position_level'] . "\n";
            $content .= "Token Name: " . $token['name'] . "\n";
            $content .= "Full Token: " . $token['full_token'] . "\n";
            $content .= "Bearer Header: Authorization: Bearer " . $token['full_token'] . "\n";
            $content .= "\n" . str_repeat('-', 80) . "\n\n";
        }

        file_put_contents($filepath, $content);
        $this->info("✅ Full tokens saved to: " . $filepath);
    }
}
