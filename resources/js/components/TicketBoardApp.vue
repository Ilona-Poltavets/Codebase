<template>
    <section class="flex h-full min-h-full flex-col rounded-2xl border border-slate-800 bg-slate-900/90 p-4 shadow-2xl backdrop-blur">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Board Workspace</h3>
                <p class="text-xs text-slate-400">Auto-updates every 5 seconds. Work from the board or switch back to the project site anytime.</p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-md border border-slate-700 px-3 py-2 text-sm text-slate-200 hover:bg-slate-800"
                    :disabled="loading"
                    @click="fetchBoard()"
                >
                    Refresh
                </button>
                <a
                    :href="createTicketUrl"
                    class="rounded-md bg-emerald-500 px-3 py-2 text-sm font-medium text-slate-950 hover:bg-emerald-400"
                >
                    New ticket
                </a>
            </div>
        </div>

        <div class="mb-4 inline-flex rounded-lg border border-slate-700 bg-slate-950/50 p-1">
            <button
                type="button"
                class="rounded-md px-3 py-2 text-sm"
                :class="currentView === 'my' ? 'bg-sky-500/20 text-sky-300' : 'text-slate-400 hover:bg-slate-800'"
                @click="switchView('my')"
            >
                My Board
            </button>
            <button
                type="button"
                class="rounded-md px-3 py-2 text-sm"
                :class="currentView === 'project' ? 'bg-sky-500/20 text-sky-300' : 'text-slate-400 hover:bg-slate-800'"
                @click="switchView('project')"
            >
                Project View
            </button>
            <button
                type="button"
                class="rounded-md px-3 py-2 text-sm"
                :class="currentView === 'developer' ? 'bg-sky-500/20 text-sky-300' : 'text-slate-400 hover:bg-slate-800'"
                @click="switchView('developer')"
            >
                Developer View
            </button>
        </div>

        <p v-if="error" class="mb-3 rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300">
            {{ error }}
        </p>

        <div v-if="loading && !columns.length" class="py-10 text-center text-sm text-slate-400">
            Loading board...
        </div>

        <div v-else class="board-workspace-scroll flex-1 overflow-x-auto">
            <div class="flex min-w-max gap-4 pb-2">
                <article
                    v-for="column in columns"
                    :key="column.id"
                    class="w-[340px] shrink-0 rounded-xl border border-slate-700 bg-slate-950/70"
                    @dragover.prevent="allowDrop(column)"
                    @drop="onDropToColumn(column.id)"
                >
                    <header class="flex items-center justify-between border-b border-slate-800 px-3 py-3">
                        <h4 class="text-sm font-semibold text-slate-100">{{ column.name }}</h4>
                        <span class="rounded-full bg-slate-900 px-2 py-1 text-xs text-slate-300">{{ column.tickets.length }}</span>
                    </header>

                    <div class="board-column-scroll max-h-[62vh] overflow-y-auto p-3 space-y-2">
                        <div
                            v-for="(ticket, index) in column.tickets"
                            :key="ticket.id"
                            class="cursor-grab rounded-lg border border-slate-700 bg-slate-900 p-3 shadow-sm transition hover:border-sky-500"
                            :draggable="currentView === 'project'"
                            :class="{ 'cursor-default': currentView !== 'project' }"
                            @dragstart="onDragStart(ticket.id, column.id)"
                            @dragover.prevent="allowDrop(column)"
                            @drop.stop="onDropToTicket(column.id, index)"
                        >
                            <a
                                :href="ticketUrl(ticket.id, ticket.project_id)"
                                class="line-clamp-2 text-sm font-medium text-slate-100 hover:text-sky-300"
                            >
                                {{ ticket.title }}
                            </a>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span
                                    v-if="ticket.priority"
                                    class="rounded-full px-2 py-1 text-[11px] font-medium"
                                    :class="priorityClass(ticket.priority.slug)"
                                >
                                    {{ ticket.priority.name }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ ticket.assignee?.name || 'Unassigned' }}
                                </span>
                                <span v-if="ticket.project_name" class="text-xs text-slate-500">
                                    {{ ticket.project_name }}
                                </span>
                                <span v-if="currentView === 'developer' && ticket.status_name" class="text-xs text-slate-500">
                                    Status: {{ ticket.status_name }}
                                </span>
                            </div>
                        </div>

                        <button
                            v-if="currentView === 'project'"
                            type="button"
                            class="w-full rounded-md border border-dashed border-slate-700 px-3 py-2 text-xs text-slate-400 hover:bg-slate-900"
                            @click="onDropToColumn(column.id)"
                        >
                            Move here
                        </button>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    fetchUrl: { type: String, required: true },
    moveUrlTemplate: { type: String, required: true },
    ticketUrlTemplate: { type: String, required: true },
    createTicketUrl: { type: String, required: true },
    initialView: { type: String, default: 'project' },
});

const columns = ref([]);
const loading = ref(true);
const error = ref('');
const allowedViews = ['project', 'developer', 'my'];
const currentView = ref(allowedViews.includes(props.initialView) ? props.initialView : 'project');
const dragState = ref({ ticketId: null, fromColumnId: null });
let pollTimer = null;

const ticketUrl = (ticketId, projectId = null) => props.ticketUrlTemplate
    .replace('__PROJECT__', String(projectId ?? '0'))
    .replace('__TICKET__', String(ticketId));
const moveUrl = (ticketId) => props.moveUrlTemplate.replace('__TICKET__', String(ticketId));

const priorityClass = (slug) => {
    if (slug === 'high' || slug === 'critical') {
        return 'bg-red-500/20 text-red-300';
    }
    if (slug === 'medium') {
        return 'bg-amber-500/20 text-amber-300';
    }
    return 'bg-slate-700 text-slate-200';
};

const fetchBoard = async (silent = false) => {
    if (!silent) {
        loading.value = true;
    }
    try {
        const { data } = await window.axios.get(props.fetchUrl, {
            params: {
                view: currentView.value,
            },
        });
        columns.value = data.columns || [];
        currentView.value = allowedViews.includes(data.view) ? data.view : currentView.value;
        error.value = '';
    } catch (e) {
        error.value = 'Cannot load board data.';
    } finally {
        loading.value = false;
    }
};

const onDragStart = (ticketId, fromColumnId) => {
    if (currentView.value !== 'project') {
        return;
    }
    dragState.value = { ticketId, fromColumnId };
};

const allowDrop = (column) => {
    if (currentView.value !== 'project' || column.kind !== 'status') {
        return;
    }
};

const removeTicketFromColumns = (ticketId) => {
    let removed = null;
    columns.value = columns.value.map((column) => {
        const nextTickets = column.tickets.filter((ticket) => {
            if (ticket.id === ticketId) {
                removed = ticket;
                return false;
            }
            return true;
        });

        return { ...column, tickets: nextTickets };
    });
    return removed;
};

const insertTicketIntoColumn = (targetColumnId, ticket, targetIndex) => {
    columns.value = columns.value.map((column) => {
        if (column.id !== targetColumnId) {
            return column;
        }
        const nextTickets = [...column.tickets];
        const safeIndex = Math.max(0, Math.min(targetIndex, nextTickets.length));
        nextTickets.splice(safeIndex, 0, { ...ticket, status_id: targetColumnId });
        return { ...column, tickets: nextTickets };
    });
};

const moveCard = async (targetColumnId, targetIndex = null) => {
    const { ticketId } = dragState.value;
    if (!ticketId || currentView.value !== 'project') {
        return;
    }

    const moved = removeTicketFromColumns(ticketId);
    if (!moved) {
        dragState.value = { ticketId: null, fromColumnId: null };
        return;
    }

    const column = columns.value.find((item) => item.id === targetColumnId);
    if (!column) {
        await fetchBoard(true);
        dragState.value = { ticketId: null, fromColumnId: null };
        return;
    }

    const indexToUse = targetIndex === null ? column.tickets.length : targetIndex;
    insertTicketIntoColumn(targetColumnId, moved, indexToUse);

    try {
        await window.axios.patch(moveUrl(ticketId), {
            status_id: targetColumnId,
            position: indexToUse,
        });
        error.value = '';
    } catch (e) {
        error.value = 'Move failed. Board reloaded.';
        await fetchBoard(true);
    } finally {
        dragState.value = { ticketId: null, fromColumnId: null };
    }
};

const onDropToColumn = (targetColumnId) => {
    if (currentView.value !== 'project') {
        return;
    }
    moveCard(targetColumnId, null);
};

const onDropToTicket = (targetColumnId, targetIndex) => {
    if (currentView.value !== 'project') {
        return;
    }
    moveCard(targetColumnId, targetIndex);
};

const switchView = async (nextView) => {
    if (currentView.value === nextView) {
        return;
    }
    currentView.value = nextView;
    dragState.value = { ticketId: null, fromColumnId: null };
    await fetchBoard();
};

onMounted(async () => {
    await fetchBoard();
    pollTimer = window.setInterval(() => {
        fetchBoard(true);
    }, 5000);
});

onBeforeUnmount(() => {
    if (pollTimer) {
        window.clearInterval(pollTimer);
    }
});
</script>

<style scoped>
.board-workspace-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(56, 189, 248, 0.75) rgba(15, 23, 42, 0.55);
}

.board-workspace-scroll::-webkit-scrollbar {
    height: 10px;
}

.board-workspace-scroll::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.55);
    border-radius: 9999px;
}

.board-workspace-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, rgba(56, 189, 248, 0.75), rgba(16, 185, 129, 0.7));
    border-radius: 9999px;
    border: 2px solid rgba(15, 23, 42, 0.7);
}

.board-workspace-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(90deg, rgba(56, 189, 248, 0.95), rgba(16, 185, 129, 0.9));
}

.board-column-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(148, 163, 184, 0.55) rgba(15, 23, 42, 0.45);
}

.board-column-scroll::-webkit-scrollbar {
    width: 8px;
}

.board-column-scroll::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.45);
    border-radius: 9999px;
}

.board-column-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.55);
    border-radius: 9999px;
    border: 1px solid rgba(15, 23, 42, 0.65);
}

.board-column-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.8);
}
</style>

