declare namespace App.Data {
    export type BranchData = {
        name: string;
        sha: string;
        repository: string;
        repositoryOwner: string;
        repositoryFullName: string;
        protected: boolean;
    };
    export type JiraTicketData = {
        key: string;
        summary: string;
        status: string;
        assignee: string | null;
        priority: string | null;
        created: string;
        updated: string;
        issueType: string;
    };
    export type LabelData = {
        id: string | number;
        name: string;
        color: string;
    };
    export type MediaData = {
        id: number;
        name: string;
        file_name: string;
        mime_type: string;
        size: number;
        disk: string;
        collection_name: string;
        custom_properties: Array<any>;
        created_at: string;
        updated_at: string;
        url: string;
        thumbnail_url: string | null;
        markdown: string;
        is_image: boolean;
        is_video: boolean;
        display_name: string;
        action_text: string;
    };
    export type PullRequestData = {
        number: number;
        title: string;
        body: string;
        state: string;
        createdAt: string;
        updatedAt: string;
        mergedAt: string | null;
        closedAt: string | null;
        isDraft: boolean;
        mergeable: string;
        url: string;
        repositoryName: string;
        repositoryOwner: string;
        repositoryFullName: string;
        user: Array<any>;
        head: Array<any>;
        base: Array<any>;
        labels: Array<any>;
        assignees: Array<any>;
        isMine: boolean;
        currentBaseSha: string | null;
        prBaseSha: string | null;
        isBehind: boolean;
        isUpToDate: boolean;
        comments: number | null;
        commits: number | null;
        changedFiles: number | null;
        additions: number | null;
        deletions: number | null;
        isMerged: boolean;
    };
    export type RecentBranchData = {
        repository: string;
        repositoryOwner: string;
        repositoryFullName: string;
        name: string;
        sha: string;
        lastCommitDate: string;
        lastCommitMessage: string;
        lastCommitAuthor: string;
        branchCreationDate: string;
        hasOpenPr: boolean;
        isRecentlyCreated: boolean;
        hoursSinceLastCommit: number;
        suggestedForPr: boolean;
        protected: boolean;
    };
    export type RepositoryData = {
        id: string | number;
        name: string;
        owner: string;
        fullName: string;
        description: string | null;
        link: string | null;
        defaultBranch: string | null;
    };
    export type ReviewerData = {
        id: string | number;
        name: string;
        login: string;
    };
    export type TemplateData = {
        id: number;
        userId: number;
        name: string;
        titleTemplate: string;
        bodyTemplate: string;
        isDefault: boolean;
        defaultLabels?: string[];
    };
}
