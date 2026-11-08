/** A blog post as listed on the index page and under "related articles". */
export interface PostSummary {
    slug: string;
    title: string;
    excerpt: string | null;
    hero_image_url: string | null;
    hero_image_srcset: string | null;
    published_at: string | null;
    author: string | null;
}

/** A full blog post on its own page. */
export interface BlogPost extends PostSummary {
    content_html: string;
}
