package pro.dite;

import org.eclipse.jgit.api.Git;
import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.lib.ObjectId;
import org.eclipse.jgit.lib.ObjectLoader;
import org.eclipse.jgit.lib.ObjectStream;
import org.eclipse.jgit.lib.Repository;
import org.eclipse.jgit.revwalk.RevCommit;
import org.eclipse.jgit.revwalk.RevTree;
import org.eclipse.jgit.revwalk.RevWalk;
import org.eclipse.jgit.storage.file.FileRepositoryBuilder;

import java.io.File;
import java.io.IOException;
import java.util.*;

abstract class HeadWalker
{

    private Repository repository;
    private Git git;

    public HeadWalker(Repository repository) throws IOException
    {
        this.repository = repository;
        git = new Git(repository);
    }

    public void walk() throws IOException, GitAPIException
    {
        ObjectId head = repository.resolve("HEAD");
        ArrayList<RevCommit> commits = new ArrayList<RevCommit>();
        for (RevCommit commit : git.log().add(head).call())
        {
            commits.add(0, commit);
        }

        Differ differ = new Differ(repository);

        // starting from oldest commit
        RevCommit parent = null;
        for (RevCommit commit : commits)
        {
            boolean skip = shouldSkipCommit(commit);
            if (!skip)
            {
//                System.out.println("\n" + commit + "; " + commit.getShortMessage());
                if (parent == null)
                {
                    for (ObjectId oid : differ.getFiles(commit.getTree()))
                    {
                        String b = getBlobContent(oid);
                        PhpFile bPhp = null;
                        try
                        {
                            bPhp = new PhpFile(b);
                            processFileDiff(commit, bPhp);
                        } catch (EmptyStackException e)
                        {
                            System.out.println("unknown file failed to parse");
                        }
                    }
                }
                else
                {
                    List<DiffEntry> diffs;
                    diffs = differ.getEdits(
                            parent.getTree().getId().toObjectId(),
                            commit.getTree().getId().toObjectId());

                    for (DiffEntry diff : diffs)
                    {
                        if (diff.getNewPath().toLowerCase().contains("vendor/")
                                || !diff.getNewPath().toLowerCase().endsWith(".php"))
                        {
                            // TODO allow other extensions as well?
                            // TODO refactor with differ
                            continue;
                        }
//                        System.out.println("\t"+diff.getNewPath());
//                        if (diff.getNewPath().contains("FeedPresenter.php"))
//                        {
//                            System.out.println("\t"+diff.getNewPath());
//                        }

                        String a = diff.getChangeType() == DiffEntry.ChangeType.ADD ? ""
                                : getBlobContent(diff.getOldId().toObjectId());
                        String b = diff.getChangeType() == DiffEntry.ChangeType.DELETE ? ""
                                : getBlobContent(diff.getNewId().toObjectId());

                        EditList edits = MyersDiff.INSTANCE.diff(RawTextComparator.WS_IGNORE_ALL, new RawText(a.getBytes()), new RawText(b.getBytes()));

                        try
                        {
                            PhpFile aPhp = new PhpFile(a);
                            PhpFile bPhp = new PhpFile(b);
                            processFileDiff(commit, edits, aPhp, bPhp);
                        } catch (EmptyStackException e)
                        {
                            System.out.println(diff.getNewPath() + " failed to parse");
                            processFileDiff(commit);
                        }
                    }
                }
            }
            onCommitDone(commit, skip);
            parent = commit;
        }
    }

    protected abstract void onCommitDone(RevCommit commit, boolean skip);

    protected abstract boolean shouldSkipCommit(RevCommit commit);

    String getBlobContent(ObjectId objectId) throws IOException
    {
        ObjectLoader object = repository.open(objectId);
        ObjectStream is = object.openStream();

        Scanner s = new Scanner(is).useDelimiter("\\A");
        return s.hasNext() ? s.next() : "";
    }

    abstract public void processFileDiff(RevCommit commit, EditList edits, PhpFile a, PhpFile b);

    abstract public void processFileDiff(RevCommit commit, PhpFile bPhp);

    abstract public void processFileDiff(RevCommit commit);

}
