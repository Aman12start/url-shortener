import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Check, Copy, ExternalLink, Link, Loader2, RefreshCw, Scissors } from 'lucide-react';

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

async function api(path, options = {}) {
    const response = await fetch(path, {
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            ...(options.headers ?? {}),
        },
        ...options,
    });

    if (response.status === 204) {
        return null;
    }

    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = body.message ?? Object.values(body.errors ?? {})?.[0]?.[0] ?? 'Something went wrong.';
        throw new Error(message);
    }

    return body;
}

function App() {
    const [longUrl, setLongUrl] = useState('');
    const [latest, setLatest] = useState(null);
    const [recentLinks, setRecentLinks] = useState([]);
    const [saving, setSaving] = useState(false);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [copied, setCopied] = useState(false);

    async function loadLinks() {
        const body = await api('/api/urls');
        setRecentLinks(body.data.slice(0, 5));
    }

    useEffect(() => {
        loadLinks()
            .catch((err) => setError(err.message))
            .finally(() => setLoading(false));
    }, []);

    async function createShortLink(event) {
        event.preventDefault();
        setSaving(true);
        setError('');
        setCopied(false);

        try {
            const body = await api('/api/urls', {
                method: 'POST',
                body: JSON.stringify({ original_url: longUrl }),
            });

            setLatest(body.data);
            setRecentLinks((current) => [body.data, ...current].slice(0, 5));
            setLongUrl('');
        } catch (err) {
            setError(err.message);
        } finally {
            setSaving(false);
        }
    }

    async function copy(shortUrl) {
        await navigator.clipboard.writeText(shortUrl);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 1300);
    }

    return (
        <main className="min-h-screen bg-[#f5f7f8] text-[#16181d]">
            <section className="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-5 py-10 sm:px-8">
                <div className="mb-8 flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-md bg-[#193b3a] text-white">
                        <Link size={19} />
                    </span>
                    <span className="text-lg font-semibold">URLShortner</span>
                </div>

                <div className="grid gap-8 lg:grid-cols-[1fr_360px] lg:items-start">
                    <section>
                        <h1 className="max-w-3xl text-4xl font-semibold leading-tight sm:text-5xl">
                            Shorten any long link in seconds.
                        </h1>
                        <p className="mt-4 max-w-2xl text-base leading-7 text-[#58616d]">
                            Paste your URL, generate a short link, copy it, and share it anywhere.
                        </p>

                        <form onSubmit={createShortLink} className="mt-8 rounded-lg border border-[#d8dde3] bg-white p-4 shadow-sm sm:p-5">
                            <div className="grid gap-3 sm:grid-cols-[1fr_auto]">
                                <label className="sr-only" htmlFor="long_url">
                                    Long URL
                                </label>
                                <input
                                    id="long_url"
                                    className="min-h-12 w-full rounded-md border border-[#cbd3dc] px-4 outline-none focus:border-[#2f7773] focus:ring-2 focus:ring-[#2f777333]"
                                    placeholder="Paste a long URL here"
                                    type="url"
                                    value={longUrl}
                                    onChange={(event) => setLongUrl(event.target.value)}
                                    required
                                />
                                <button
                                    className="inline-flex min-h-12 items-center justify-center gap-2 rounded-md bg-[#193b3a] px-5 font-semibold text-white transition hover:bg-[#2f615e] disabled:cursor-not-allowed disabled:opacity-70"
                                    disabled={saving}
                                >
                                    {saving ? <Loader2 className="animate-spin" size={18} /> : <Scissors size={18} />}
                                    Shorten
                                </button>
                            </div>

                            {error && <p className="mt-4 rounded-md bg-[#fff0ed] px-3 py-2 text-sm text-[#9d2d1f]">{error}</p>}

                            {latest && (
                                <div className="mt-5 rounded-md border border-[#cfe3df] bg-[#f0faf7] p-4">
                                    <p className="text-sm font-medium text-[#315b58]">Your short link is ready</p>
                                    <div className="mt-3 grid gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-center">
                                        <a className="truncate font-semibold text-[#12645f] hover:underline" href={latest.short_url} target="_blank" rel="noreferrer">
                                            {latest.short_url}
                                        </a>
                                        <IconButton label="Copy" onClick={() => copy(latest.short_url)}>
                                            {copied ? <Check size={17} /> : <Copy size={17} />}
                                        </IconButton>
                                        <IconButton label="Open" href={latest.short_url}>
                                            <ExternalLink size={17} />
                                        </IconButton>
                                    </div>
                                </div>
                            )}
                        </form>
                    </section>

                    <aside className="rounded-lg border border-[#d8dde3] bg-white shadow-sm">
                        <div className="flex items-center justify-between border-b border-[#e4e8ed] px-4 py-3">
                            <h2 className="font-semibold">Recent links</h2>
                            <button
                                className="inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#cbd3dc] text-[#27303a] hover:bg-[#f2f5f7]"
                                onClick={() => loadLinks().catch((err) => setError(err.message))}
                                title="Refresh"
                                aria-label="Refresh"
                            >
                                <RefreshCw size={16} />
                            </button>
                        </div>
                        {loading ? (
                            <div className="flex min-h-40 items-center justify-center text-sm text-[#5d6672]">
                                <Loader2 className="mr-2 animate-spin" size={17} />
                                Loading
                            </div>
                        ) : recentLinks.length === 0 ? (
                            <p className="px-4 py-8 text-sm text-[#5d6672]">Shortened links will appear here.</p>
                        ) : (
                            <div className="divide-y divide-[#e4e8ed]">
                                {recentLinks.map((url) => (
                                    <article key={url.id} className="px-4 py-3">
                                        <a className="block truncate font-semibold text-[#12645f] hover:underline" href={url.short_url} target="_blank" rel="noreferrer">
                                            {url.short_url}
                                        </a>
                                        <p className="mt-1 truncate text-xs text-[#707985]">{url.original_url}</p>
                                        <p className="mt-2 text-xs font-medium text-[#4f5965]">{url.clicks_count} clicks</p>
                                    </article>
                                ))}
                            </div>
                        )}
                    </aside>
                </div>
            </section>
        </main>
    );
}

function IconButton({ children, label, href, onClick }) {
    const className = 'inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#cbd3dc] bg-white text-[#27303a] transition hover:bg-[#f2f5f7]';

    if (href) {
        return (
            <a className={className} href={href} target="_blank" rel="noreferrer" title={label} aria-label={label}>
                {children}
            </a>
        );
    }

    return (
        <button className={className} type="button" onClick={onClick} title={label} aria-label={label}>
            {children}
        </button>
    );
}

const root = document.getElementById('app');

if (root) {
    createRoot(root).render(<App />);
}
