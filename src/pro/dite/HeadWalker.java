package pro.dite;

import org.eclipse.jgit.api.Git;
import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.lib.ObjectId;
import org.eclipse.jgit.lib.ObjectLoader;
import org.eclipse.jgit.lib.ObjectStream;
import org.eclipse.jgit.lib.Repository;
import org.eclipse.jgit.revwalk.RevCommit;
import org.eclipse.jgit.storage.file.FileRepositoryBuilder;

import java.io.File;
import java.io.IOException;
import java.util.*;

abstract class HeadWalker
{

    private Repository repository;
    private Git git;

    public HeadWalker(File gitDir) throws IOException
    {
        FileRepositoryBuilder builder = new FileRepositoryBuilder();
        repository = builder.setGitDir(gitDir)
                .readEnvironment() // scan environment GIT_* variables
                .findGitDir() // scan up the file system tree
                .build();
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

//                    System.out.println(diff.getNewPath());
//                    if (diff.getNewPath().contains("WebGuy"))
//                    {
//                        System.out.println("hit");
//                    }

                    try
                    {
                        PhpFile aPhp = new PhpFile(a);
                        PhpFile bPhp = new PhpFile(b);
                        processFileDiff(commit, edits, aPhp, bPhp);
                    } catch (EmptyStackException e)
                    {
                        System.out.println("Failed parsing");
                        System.out.println(diff.getNewPath());
                    }
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

    abstract public void processFileDiff(RevCommit commit, EditList edits, PhpFile a, PhpFile b);

}
