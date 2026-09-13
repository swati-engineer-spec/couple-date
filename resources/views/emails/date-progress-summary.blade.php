<!doctype html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #4c2b39; line-height: 1.6;">
    <h2>{{ $progress->completed ? 'It is a date! ♥' : 'Date invitation progress update' }}</h2>
    <p>Someone reached step {{ $progress->last_step }} of 5: <strong>{{ $progress->last_step_name }}</strong>.</p>
    <p><strong>Steps completed:</strong> {{ implode(', ', $progress->steps_completed ?? []) ?: 'none yet' }}</p>
    <p><strong>Date:</strong> {{ $progress->chosen_date?->format('l, F j, Y') ?? 'Not selected' }}</p>
    <p><strong>Time:</strong> {{ $progress->chosen_time ?: 'Not selected' }}</p>
    <p><strong>Food choice:</strong> {{ $progress->chosen_option ?: 'Not selected' }}</p>
    <p><strong>Finished:</strong> {{ $progress->completed ? 'Yes' : 'No' }}</p>
    <p><strong>Left before finishing:</strong> {{ $progress->abandoned ? 'Yes' : 'No' }}</p>
</body>
</html>
