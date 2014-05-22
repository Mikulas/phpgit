package pro.dite;

import com.sun.org.apache.xpath.internal.operations.And;
import org.eclipse.jgit.api.Git;
import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.errors.AmbiguousObjectException;
import org.eclipse.jgit.errors.CorruptObjectException;
import org.eclipse.jgit.errors.IncorrectObjectTypeException;
import org.eclipse.jgit.errors.MissingObjectException;
import org.eclipse.jgit.lib.ObjectId;
import org.eclipse.jgit.lib.ObjectLoader;
import org.eclipse.jgit.lib.ObjectStream;
import org.eclipse.jgit.lib.Repository;
import org.eclipse.jgit.revwalk.RevCommit;
import org.eclipse.jgit.revwalk.RevTree;
import org.eclipse.jgit.revwalk.RevWalk;
import org.eclipse.jgit.storage.file.FileRepositoryBuilder;
import org.eclipse.jgit.treewalk.TreeWalk;
import org.eclipse.jgit.treewalk.WorkingTreeIterator;
import org.eclipse.jgit.treewalk.filter.*;
import org.eclipse.jgit.util.io.DisabledOutputStream;
import org.eclipse.jgit.util.io.NullOutputStream;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.IOException;
import java.util.*;

abstract public class HeadWalker
{

    Repository repository;
    Git git;
    RevWalk walk;
    Differ differ;

    public HeadWalker(File gitDir) throws IOException
    {
        FileRepositoryBuilder builder = new FileRepositoryBuilder();
        repository = builder.setGitDir(gitDir)
                .readEnvironment() // scan environment GIT_* variables
                .findGitDir() // scan up the file system tree
                .build();
        git = new Git(repository);
        walk = new RevWalk(repository);
    }

    public void walk() throws IOException, GitAPIException
    {
        ObjectId head = repository.resolve("HEAD");
        ArrayList<RevCommit> commits = new ArrayList<RevCommit>();
        for (RevCommit commit : git.log().add(head).call())
        {
            commits.add(0, commit);
        }

        differ = new Differ(repository);

        // starting from oldest commit
        RevCommit parent = null;
        for (RevCommit commit : commits)
        {
            if (parent != null)
            {
                List<DiffEntry> diffs = differ.getEdits(
                    parent.getTree().getId().toObjectId(),
                    commit.getTree().getId().toObjectId());

                for (DiffEntry diff : diffs)
                {
                    if (!diff.getNewPath().toLowerCase().endsWith(".php"))
                    {
                        // TODO allow other extensions as well?
                        continue;
                    }

                    String a = diff.getChangeType() == DiffEntry.ChangeType.ADD ? ""
                        : getBlobContent(diff.getOldId().toObjectId());
                    String b = diff.getChangeType() == DiffEntry.ChangeType.DELETE ? ""
                        : getBlobContent(diff.getNewId().toObjectId());

                    EditList edits = MyersDiff.INSTANCE.diff(RawTextComparator.WS_IGNORE_ALL, new RawText(a.getBytes()), new RawText(b.getBytes()));

                    //System.out.println(diff.getNewPath());

                    try
                    {
                        PhpFile aPhp = new PhpFile(a);
                        PhpFile bPhp = new PhpFile(b);
                    } catch (EmptyStackException e)
                    {
                        System.out.println("Failed parsing");
                        System.out.println(diff.getNewPath());
                    }

                    processFileDiff(commit, edits, a, b);
                }
            }
            parent = commit;
        }
    }

    String getBlobContent(ObjectId objectId) throws IOException
    {
        ObjectLoader object = repository.open(objectId);
        ObjectStream is = object.openStream();

        Scanner s = new Scanner(is).useDelimiter("\\A");
        return s.hasNext() ? s.next() : "";
    }

    abstract public void processFileDiff(RevCommit commit, EditList edits, String a, String b) throws IOException;

}
