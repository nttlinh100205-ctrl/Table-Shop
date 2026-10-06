// Keep acknowledged and pending messages when an older polling response arrives.
function mergeChatMessages(current, incoming) {
    const messages = new Map(current.map(message => [String(message.id), message]));
    incoming.forEach(message => messages.set(String(message.id), message));
    return [...messages.values()].sort((a, b) => {
        const aPending = String(a.id).startsWith('temp_'), bPending = String(b.id).startsWith('temp_');
        return aPending !== bPending ? Number(aPending) - Number(bPending) : aPending ? 0 : Number(a.id) - Number(b.id);
    });
}
